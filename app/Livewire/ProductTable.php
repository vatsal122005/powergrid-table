<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class ProductTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'product-table';

    public function setUp(): array
    {
        Log::info('ProductTable::setUp called');
        $this->showCheckBox();

        $userId = Auth::id();
        $this->persist(['columns', 'filters'], prefix: (string) ($userId ?? ''));

        Log::info('Setting up PowerGrid');

        return [
            PowerGrid::exportable('products_export')
                ->striped()
                ->columnWidth([3 => 30])
                ->queues(1)
                ->onQueue('default')
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSoftDeletes()
                ->withoutLoading()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('livewire.product-table-header'),
            PowerGrid::footer()
                ->showPerPage(10, [0, 20, 50, 100])
                ->pageName('productpage')
                ->showRecordCount(),
            // PowerGrid::responsive(),
        ];
    }

    public function datasource(): Builder
    {
        return Product::query()
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')
            ->leftJoin('users', 'products.user_id', '=', 'users.id')
            ->select([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.stock_quantity',
                'products.category_id',
                'categories.name as category_name',
                'sub_categories.name as sub_category_name',
                'products.status',
                'products.sku',
                'products.image_url',
                'products.meta_title',
                'products.meta_description',
                'products.user_id',
                'users.name as user_name',
                'products.created_at',
                'products.deleted_at'
            ])
            ->withTrashed();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG();

        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('description', fn($p) => e($p->description)) // raw string for export
            ->add('description_excerpt', function ($product) {
                $excerpt = str(e($product->description))->limit(10);
                $full = e($product->description);
                return "<span title=\"{$full}\">{$excerpt}</span>";
            })
            ->add('price')
            ->add('stock_quantity')
            ->add('category_id')
            ->add('category_name')
            ->add('sub_category_name')
            ->add('status', function ($product) {
                return [
                    ($product->status === 'active') ? 'check-circle' : 'x-circle' => [],
                ];
            })
            ->add('status_text', function ($product) {
                return $product->status === 'active' ? 'Active' : 'Inactive';
            })
            ->add('sku')
            ->add('sku_barcode', function (Product $product) use ($barcodeGenerator) {
                try {
                    return $product->sku
                        ? sprintf(
                            '<img src="data:image/png;base64,%s">',
                            e(base64_encode($barcodeGenerator->getBarcode($product->sku, $barcodeGenerator::TYPE_CODE_128)))
                        )
                        : '';
                } catch (\Throwable $e) {
                    Log::error('Barcode generation failed for product ID ' . $product->id . ': ' . $e->getMessage());

                    return '<span title="Barcode generation failed">' . __('messages.barcode_not_found') . '</span>';
                }
            })
            ->add('image_url', fn($product) => $product->image_url
                ? '<img src="' . e(asset('storage/' . e($product->image_url))) . '" alt="Product Image" style="max-width:60px;max-height:60px;border-radius:6px;">'
                : '')
            ->add('meta_title', fn($p) => e($p->meta_title))
            ->add('meta_description', fn($p) => e($p->meta_description)) // for export
            ->add('meta_description_excerpt', function ($product) {
                $excerpt = str(e($product->meta_description))->limit(20);
                $full = e($product->meta_description);
                return "<span title=\"{$full}\">{$excerpt}</span>";
            })

            ->add('created_at')
            ->add('deleted_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Description', 'description_excerpt')
                ->visibleInExport(false),
            Column::make('Description', 'description')
                ->hidden()
                ->sortable()
                ->searchable()
                ->visibleInExport(true),
            Column::make('Price', 'price')
                ->sortable()
                ->searchable(),
            Column::make('Stock quantity', 'stock_quantity')
                ->sortable()
                ->searchable(),
            Column::make('Category', 'category_name', 'category_id')
                ->sortable()
                ->searchable(),
            Column::make('Sub Category', 'sub_category_name', 'sub_category_id')
                ->sortable()
                ->searchable(),
            Column::make('Status', 'status')
                ->template()
                ->sortable()
                ->searchable()
                ->visibleInExport(false),
            Column::make('Status', 'status_text')
                ->hidden()
                ->visibleInExport(true),
            Column::make('Sku', 'sku')
                ->sortable()
                ->searchable(),
            Column::make('Image', 'image_url')
                ->visibleInExport(false),
            Column::make('Sku barcode', 'sku_barcode')
                ->visibleInExport(false),
            Column::make('Meta title', 'meta_title')
                ->sortable()
                ->searchable(),
            Column::make('Meta description', 'meta_description_excerpt')
                ->visibleInExport(false),
            Column::make('Meta Description', 'meta_description')
                ->hidden()
                ->sortable()
                ->searchable()
                ->visibleInExport(true),
            Column::make('User Name', 'user_name', 'user_id')
                ->sortable()
                ->searchable(),
            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchableRaw('DATE_FORMAT(products.created_at, "%d/%m/%Y") like ?'),
            // Column::make('Deleted', 'deleted_at')
            //     ->visibleInExport(false)
            //     ->sortable()
            //     ->html(
            //         fn($row) => $row->deleted_at
            //             ? '<span class="text-red-500 font-semibold">Deleted</span>'
            //             : '<span class="text-green-500 font-semibold">Active</span>'
            //     )
            //     ->searchableRaw('DATE_FORMAT(products.deleted_at, "%d/%m/%Y") like ?'),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name', 'products.name')
                ->operators(['contains']),
            Filter::inputText('sku', 'products.sku')
                ->operators(['contains']),

            // Parent filter
            Filter::select('category_name', 'products.category_id') // Note: using categories.id
                ->dataSource(Category::query()
                    ->select('id', 'name')
                    ->get())
                ->optionLabel('name')
                ->optionValue('id'),

            Filter::select('sub_category_name', 'products.sub_category_id')
                ->depends(['category_name'])
                ->dataSource(function ($depends) {
                    // If no categories selected or empty, return empty collection
                    if (!isset($depends['category_name']) || empty($depends['category_name'])) {
                        return collect([]);
                    }

                    $categoryIds = $depends['category_name'];

                    // Handle different data structures
                    if (is_string($categoryIds)) {
                        $categoryIds = [$categoryIds];
                    } elseif (is_array($categoryIds)) {
                        // Remove any empty values and flatten
                        $categoryIds = collect($categoryIds)
                            ->flatten()
                            ->filter(function ($value) {
                                return !is_null($value) && $value !== '';
                            })
                            ->unique()
                            ->values()
                            ->all();
                    }

                    // If still empty after processing, return empty
                    if (empty($categoryIds)) {
                        return collect([]);
                    }

                    return SubCategory::query()
                        ->whereIn('category_id', $categoryIds)
                        ->select('id', 'name')
                        ->orderBy('name')
                        ->get();
                })
                ->optionLabel('name')
                ->optionValue('id'),
            Filter::select('status', 'products.status')
                ->dataSource([
                    ['id' => 'active', 'name' => 'Active'],
                    ['id' => 'inactive', 'name' => 'Inactive'],
                ])
                ->optionLabel('name')
                ->optionValue('id'),
            Filter::inputText('price', 'Price')
                ->operators(['contains']),
            Filter::inputText('stock_quantity', 'products.stock_quantity')
                ->operators(['contains']),
            Filter::inputText('meta_title', 'products.meta_title')
                ->operators(['contains']),
            Filter::inputText('meta_description', 'products.meta_description')
                ->operators(['contains']),
            Filter::inputText('user_name', 'users.name')
                ->operators(['contains']),
            Filter::datetimepicker('created_at', 'products.created_at')
                ->params([
                    'timezone' => 'India/Kolkata',
                ]),
        ];
    }

    public function rowTemplates(): array
    {
        return [
            'check-circle' => '
            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="size-4 mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round"
                         d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                Active
            </span>',

            'x-circle' => '
            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="size-4 mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round"
                         d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                Inactive
            </span>',
        ];
    }


    #[On('edit')]
    public function edit($rowId)
    {
        Log::info('Edit product livewire called with id: ' . $rowId);
        if (! is_numeric($rowId)) {
            Log::error('Invalid id passed to edit product livewire');

            return;
        }

        $product = Product::find($rowId);
        if (! $product) {
            Log::error('Product not found with id: ' . $rowId);
            session()->flash('error', __('messages.product_not_found'));

            return;
        }

        $this->authorize('update', $product);

        Log::info('Redirecting to edit product page with id: ' . $rowId);

        return redirect()->route('products.edit', ['id' => $rowId]);
    }

    #[On('show')]
    public function show($rowId)
    {
        if (! is_numeric($rowId)) {
            return;
        }
        $this->authorize('view', Product::find($rowId));

        return redirect()->route('products.show', ['id' => $rowId]);
    }

    #[On('delete')]
    public function delete(int $rowId, bool $confirmed = false)
    {
        $product = Product::find($rowId);

        if (! $product) {
            return $this->dispatch('deleteCompleted', [
                'success' => false,
                'message' => __('messages.not_found'),
            ]);
        }

        $this->authorize('delete', $product);

        if (! $confirmed) {
            return $this->dispatch('confirm', [
                'title' => __('messages.delete_confirm_title'),
                'message' => __('messages.delete_confirm_message', ['name' => e($product->name)]),
                'icon' => 'warning',
                'confirmButtonText' => __('messages.confirm_yes'),
                'cancelButtonText' => __('messages.confirm_cancel'),
                'confirmEvent' => 'delete',
                'confirmPayload' => ['rowId' => $rowId, 'confirmed' => true],
            ]);
        }

        try {
            $product->delete();
            $this->dispatch('deleteCompleted', [
                'success' => true,
                'message' => __('messages.delete_success', ['name' => $product->name]),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('deleteCompleted', [
                'success' => false,
                'message' => __('messages.delete_failed'),
            ]);
        }
    }

    #[On('restore')]
    public function restore(int $rowId, bool $confirmed = false)
    {
        $product = Product::withTrashed()->find($rowId);

        if (! $product) {
            return $this->dispatch('restoreCompleted', [
                'success' => false,
                'message' => __('messages.not_found'),
            ]);
        }

        $this->authorize('restore', $product);

        if (! $confirmed) {
            return $this->dispatch('confirm', [
                'title' => __('messages.restore_confirm_title', ['name' => e($product->name)]),
                'message' => __('messages.restore_confirm_message', ['name' => e($product->name)]),
                'icon' => 'info',
                'confirmButtonText' => __('messages.confirm_yes'),
                'cancelButtonText' => __('messages.confirm_cancel'),
                'confirmEvent' => 'restore',
                'confirmPayload' => ['rowId' => $rowId, 'confirmed' => true],
            ]);
        }

        try {
            $product->restore();
            $this->dispatch('restoreCompleted', [
                'success' => true,
                'message' => __('messages.restore_success', ['name' => $product->name]),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('restoreCompleted', [
                'success' => false,
                'message' => __('messages.restore_failed'),
            ]);
        }
    }

    #[On('forceDelete')]
    public function forceDelete(int $rowId, bool $confirmed = false): void
    {
        $product = Product::withTrashed()->find($rowId);

        if (! $product) {
            $this->dispatch('forceDeleteCompleted', [
                'success' => false,
                'message' => __('messages.not_found'),
            ]);
            return;
        }

        // Authorization check
        $this->authorize('forceDelete', $product);

        if (! $confirmed) {
            $this->dispatch('confirm', [
                'title'            => __('messages.force_delete_confirm_title', ['name' => e($product->name)]),
                'message'          => __('messages.force_delete_confirm_message', ['name' => e($product->name)]),
                'icon'             => 'warning',
                'confirmButtonText' => __('messages.confirm_yes'),
                'cancelButtonText' => __('messages.confirm_cancel'),
                'confirmEvent'     => 'forceDelete',
                'confirmPayload'   => ['rowId' => $rowId, 'confirmed' => true],
            ]);
            return;
        }

        try {
            $product->forceDelete();

            $this->dispatch('forceDeleteCompleted', [
                'success' => true,
                'message' => __('messages.force_delete_success', ['name' => $product->name]),
            ]);
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch('forceDeleteCompleted', [
                'success' => false,
                'message' => __('messages.force_delete_failed'),
            ]);
        }
    }

    #[On('download')]
    public function download($rowId)
    {
        if (! is_numeric($rowId)) {
            Log::error('Download failed: Invalid product ID.');
            session()->flash('error', __('messages.invalid_id'));

            return;
        }

        $product = Product::find($rowId);
        if (! $product || ! $product->image_url) {
            Log::error('Download failed: Product image not found.');
            session()->flash('error', __('messages.image_not_found'));

            return;
        }

        $imagePath = $product->image_url;
        if (! Storage::disk('public')->exists($imagePath)) {
            Log::error('Download failed: Image file does not exist.');
            session()->flash('error', __('messages.image_missing'));

            return;
        }

        $fullPath = storage_path('app/public/' . $imagePath);

        $resolvedFullPath = realpath($fullPath);
        $publicPath = realpath(storage_path('app/public'));

        if (! Str::startsWith($resolvedFullPath, $publicPath)) {
            Log::error('Download failed: Invalid file path.');
            session()->flash('error', __('messages.invalid_path'));

            return;
        }

        Log::info('Download started: ' . $imagePath);

        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $safeName = 'product-image-' . $product->id . ($extension ? '.' . $extension : '');

        $response = response()->download($fullPath, $safeName);
        Log::info('Download completed: ' . $imagePath);

        return $response;
    }

    public function actions(Product $row): array
    {
        $buttonClass = 'px-2 py-1 rounded-md transition duration-150 ease-in-out 
        flex items-center gap-1 text-sm 
        dark:ring-pg-primary-600 dark:border-pg-primary-600 
        dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 
        dark:text-pg-primary-300 dark:bg-pg-primary-700';

        $buttons = [];

        $user = Auth::user(); // no guard() needed unless you use custom guards

        // ✅ Only run authorization if a user exists
        if ($user && Gate::forUser($user)->allows('update', $row)) {
            $buttons[] = Button::add('edit')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>')
                ->tooltip('Edit')
                ->class($buttonClass . ' text-blue-600 hover:text-blue-800')
                ->dispatch('edit', ['rowId' => $row->id]);
        }

        if ($user && Gate::forUser($user)->allows('view', $row)) {
            $buttons[] = Button::add('show')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>')
                ->tooltip('Show')
                ->class($buttonClass . ' text-green-600 hover:text-green-800')
                ->dispatch('show', ['rowId' => $row->id]);
        }

        if (!is_null($row->deleted_at)) {
            // Show Restore if product is soft-deleted
            if ($user && Gate::forUser($user)->allows('restore', $row)) {
                $buttons[] = Button::add('restore')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-undo-icon lucide-undo"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>')
                    ->tooltip('Restore')
                    ->class($buttonClass . ' bg-green-500 hover:text-green-800')
                    ->dispatch('restore', ['rowId' => $row->id]);
            }

            // Show Force Delete if product is soft-deleted
            if ($user && Gate::forUser($user)->allows('forceDelete', $row)) {
                $buttons[] = Button::add('forceDelete')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x-icon lucide-circle-x"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>')
                    ->tooltip('Force Delete')
                    ->class($buttonClass . ' text-red-600 hover:text-red-800')
                    ->dispatch('forceDelete', ['rowId' => $row->id]);
            }
        } else {
            // Show Delete if product is active
            if ($user && Gate::forUser($user)->allows('delete', $row)) {
                $buttons[] = Button::add('delete')
                    ->slot('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-icon lucide-trash"><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>')
                    ->tooltip('Delete')
                    ->class($buttonClass . ' text-red-600 hover:text-red-800')
                    ->dispatch('delete', ['rowId' => $row->id]);
            }

            // ✅ Always include "Download" (needed for exports to succeed)
            $buttons[] = Button::add('download')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>')
                ->tooltip('Download Product Image')
                ->class($buttonClass . ' text-indigo-600 hover:text-indigo-800')
                ->dispatch('download', ['rowId' => $row->id]);
        }
        return $buttons;
    }
}
