<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public $realprimarykey = 'id'; // Define the real primary key

    public string $tableName = 'product-table';

    public function setUp(): array
    {
        Log::info('ProductTable::setUp called');
        $this->showCheckBox();

        Log::info('Setting up PowerGrid');

        return [
            PowerGrid::exportable('products_export')
                ->striped()
                ->columnWidth([3 => 30])
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV)
                ->queues(1)
                ->onQueue('default'),
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('livewire.product-table-header'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
            // PowerGrid::responsive(),
        ];
    }

    public function datasource(): Builder
    {
        return Product::query()
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('users', 'products.user_id', '=', 'users.id')
            ->select([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.stock_quantity',
                'products.category_id',
                'categories.name as category_name',
                'products.status',
                'products.sku',
                'products.image_url',
                'products.meta_title',
                'products.meta_description',
                'products.user_id',
                'users.name as user_name',
                'products.created_at',
            ]);
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
            ->add('description_excerpt', function ($product) {
                $excerpt = str(e($product->description))->limit(10);
                $full = e($product->description);

                return "<span title=\"{$full}\">{$excerpt}</span>";
            })
            ->add('price')
            ->add('stock_quantity')
            ->add('category_id')
            ->add('category_name')
            ->add('status')
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
            ->add('image_url', fn ($product) => $product->image_url
                ? '<img src="' . e(asset('storage/' . e($product->image_url))) . '" alt="Product Image" style="max-width:60px;max-height:60px;border-radius:6px;">'
                : '')
            ->add('meta_title', fn ($p) => e($p->meta_title))
            ->add('meta_description_excerpt', function ($product) {
                $excerpt = str(e($product->meta_description))->limit(20);
                $full = e($product->meta_description);

                return "<span title=\"{$full}\">{$excerpt}</span>";
            })
            ->add('created_at');
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
            Column::make('Category', 'category_name', 'categories.name')
                ->sortable()
                ->searchable(),
            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),
            Column::make('Sku', 'sku')
                ->sortable()
                ->searchable(),
            Column::make('Image url', 'image_url')
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
            Column::make('User Name', 'user_name', 'users.name')
                ->sortable()
                ->searchable(),
            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),
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
            Filter::multiSelect('category_name', 'products.category_id')
                ->dataSource(
                    \App\Models\Category::query()
                        ->get()
                        ->map(fn ($cat) => ['id' => $cat->id, 'name' => $cat->name])
                        ->toArray()
                )
                ->optionValue('id')
                ->optionLabel('name'),
            Filter::boolean('status', 'products.status')
                ->label('Inactive', 'Active'),
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

    #[\Livewire\Attributes\On('edit')]
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

    #[\Livewire\Attributes\On('show')]
    public function show($rowId)
    {
        if (! is_numeric($rowId)) {
            return;
        }
        $this->authorize('view', Product::find($rowId));

        return redirect()->route('products.show', ['id' => $rowId]);
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId, $confirmed = false)
    {
        Log::info('Delete product livewire called with id: ' . $rowId . ' and confirmed: ' . $confirmed);
        $this->authorize('delete', Product::find($rowId));
        if (! $this->isValidId($rowId)) {
            return $this->dispatchDeleteCompleted(false, __('messages.invalid_id'));
        }

        $product = $this->findProduct($rowId);
        if (! $product) {
            return $this->dispatchDeleteCompleted(false, __('messages.not_found'));
        }

        if (! $confirmed) {
            $this->dispatchDeleteConfirm($product, $rowId);

            return;
        }

        $this->performDelete($product);
    }

    /**
     * Validate ID.
     */
    private function isValidId($id): bool
    {
        return is_numeric($id) && $id > 0;
    }

    /**
     * Find Product by ID.
     */
    private function findProduct(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * Dispatch confirmation modal.
     */
    private function dispatchDeleteConfirm(Product $product, int $rowId): void
    {
        $this->dispatch('confirm', [
            'title' => __('messages.delete_confirm_title'),
            'message' => __('messages.delete_confirm_message', ['name' => e($product->name)]),
            'icon' => 'warning',
            'confirmButtonText' => __('messages.confirm_yes'),
            'cancelButtonText' => __('messages.confirm_cancel'),
            'confirmEvent' => 'delete',
            'confirmPayload' => ['rowId' => $rowId, 'confirmed' => true],
        ]);
    }

    /**
     * Perform product deletion with exception handling.
     */
    private function performDelete(Product $product): void
    {
        try {
            $this->deleteImage($product);
            $productName = $product->name;
            $product->delete();

            $this->dispatchDeleteCompleted(true, __('messages.delete_success', ['name' => $productName]));
        } catch (\Throwable $e) {
            report($e); // Centralized logging
            $this->dispatchDeleteCompleted(false, __('messages.delete_error'));
        }
    }

    /**
     * Delete product image if exists.
     */
    private function deleteImage(Product $product): void
    {
        if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
            Storage::disk('public')->delete($product->image_url);
        }
    }

    /**
     * Dispatch delete completed event.
     */
    private function dispatchDeleteCompleted(bool $success, string $message)
    {
        $this->dispatch('deleteCompleted', compact('success', 'message'));
    }

    #[\Livewire\Attributes\On('download')]
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
        $buttonClass = 'pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 
        dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 
        dark:text-pg-primary-300 dark:bg-pg-primary-700';

        $user = auth()->guard()->user();
        $buttons = [];

        if ($user->can('update', $row)) {
            $buttons[] = Button::add('edit')
                ->slot('<i class="fas fa-edit"></i> Edit')
                ->class($buttonClass . ' text-blue-600 hover:text-blue-800')
                ->dispatch('edit', ['rowId' => $row->id]);
        }

        if ($user->can('view', $row)) {
            $buttons[] = Button::add('show')
                ->slot('<i class="fas fa-eye"></i> Show')
                ->class($buttonClass . ' text-green-600 hover:text-green-800')
                ->dispatch('show', ['rowId' => $row->id]);
        }

        if ($user->can('delete', $row)) {
            $buttons[] = Button::add('delete')
                ->slot('<i class="fas fa-trash"></i> Delete')
                ->class($buttonClass . ' text-red-600 hover:text-red-800')
                ->dispatch('delete', ['rowId' => $row->id]);
        }

        $buttons[] = Button::add('download')
            ->slot('<i class="fas fa-download"></i> Download Image')
            ->class($buttonClass . ' text-indigo-600 hover:text-indigo-800')
            ->dispatch('download', ['rowId' => $row->id]);

        return $buttons;
    }
}
