<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class ProductTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'product-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('products_export_' . Carbon::now()->timestamp)
                ->striped()
                ->columnWidth([2 => 30])
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('livewire.product-table-header'),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Product::query()
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
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
                'products.created_at'
            ]);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG;

        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('description')
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
                            base64_encode($barcodeGenerator->getBarcode($product->sku, $barcodeGenerator::TYPE_CODE_128))
                        )
                        : '';
                } catch (\Throwable $e) {
                    Log::error('Barcode generation failed for product ID ' . $product->id . ': ' . $e->getMessage());
                    return '';
                }
            })
            ->add('image_url', fn($product) => $product->image_url
                ? '<img src="' . e(asset('storage/' . $product->image_url)) . '" alt="Product Image" style="max-width:60px;max-height:60px;border-radius:6px;">'
                : '')
            ->add('meta_title', fn($p) => e($p->meta_title))
            ->add('meta_description')
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
            Column::make('Description', 'description')
                ->hidden()
                ->visibleInExport(true),
            Column::make('Description', 'description_excerpt')
                ->sortable()
                ->searchable()
                ->visibleInExport(false),
            Column::make('Price', 'price')
                ->sortable()
                ->searchable(),
            Column::make('Stock quantity', 'stock_quantity')
                ->sortable()
                ->searchable(),
            Column::make('Category', 'category_name')
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
            Column::make('Meta description', 'meta_description')
                ->hidden()
                ->visibleInExport(true),
            Column::make('Meta description', 'meta_description_excerpt')
                ->sortable()
                ->searchable()
                ->visibleInExport(false),
            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),
            Column::action('Action')
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
                        ->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name])
                        ->toArray()
                )
                ->optionValue('id')
                ->optionLabel('name'),
            Filter::select('status', 'Status')
                ->dataSource([
                    ['id' => 'active', 'name' => 'Active'],
                    ['id' => 'inactive', 'name' => 'Inactive'],
                ])
                ->optionValue('id')->optionLabel('name'),
            Filter::inputText('price', 'Price')
                ->operators(['contains']),
            Filter::inputText('stock_quantity', 'products.stock_quantity')
                ->operators(['contains']),
            Filter::inputText('meta_title', 'products.meta_title')
                ->operators(['contains']),
            Filter::inputText('meta_description', 'products.meta_description')
                ->operators(['contains']),
            Filter::datetimepicker('created_at', 'products.created_at'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId)
    {
        if (!is_numeric($rowId))
            return;
        return redirect()->route('products.edit', ['id' => $rowId]);
    }

    #[\Livewire\Attributes\On('show')]
    public function show($rowId)
    {
        if (!is_numeric($rowId))
            return;
        return redirect()->route('products.show', ['id' => $rowId]);
    }

    #[\Livewire\Attributes\On('delete')]
    public function delete($rowId, $confirmed = false)
    {
        if (!$this->isValidId($rowId)) {
            return $this->dispatchDeleteCompleted(false, __('messages.invalid_id'));
        }

        $product = $this->findProduct($rowId);
        if (!$product) {
            return $this->dispatchDeleteCompleted(false, __('messages.not_found'));
        }

        if (!$confirmed) {
            return $this->dispatchDeleteConfirm($product, $rowId);
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
            'title'             => __('messages.delete_confirm_title'),
            'message'           => __('messages.delete_confirm_message', ['name' => e($product->name)]),
            'icon'              => 'warning',
            'confirmButtonText' => __('messages.confirm_yes'),
            'cancelButtonText'  => __('messages.confirm_cancel'),
            'confirmEvent'      => 'delete',
            'confirmPayload'    => ['rowId' => $rowId, 'confirmed' => true],
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
    private function dispatchDeleteCompleted(bool $success, string $message): void
    {
        $this->dispatch('deleteCompleted', compact('success', 'message'));
    }

    #[\Livewire\Attributes\On('download')]
    public function download($rowId)
    {
        if (!is_numeric($rowId)) {
            Log::error('Download failed: Invalid product ID.');
            session()->flash('error', 'Invalid product ID.');
            return;
        }

        $product = Product::find($rowId);
        if (!$product || !$product->image_url) {
            Log::error('Download failed: Product image not found.');
            session()->flash('error', 'Product image not found.');
            return;
        }

        $imagePath = $product->image_url;
        if (!Storage::disk('public')->exists($imagePath)) {
            Log::error('Download failed: Image file does not exist.');
            session()->flash('error', 'Image file does not exist.');
            return;
        }

        $fullPath = storage_path('app/public/' . $imagePath);

        $resolvedFullPath = realpath($fullPath);
        $publicPath = realpath(storage_path('app/public'));

        if (!Str::startsWith($resolvedFullPath, $publicPath)) {
            Log::error('Download failed: Invalid file path.');
            session()->flash('error', 'Invalid file path.');
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
        $buttonClass = 'pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700';

        return [
            Button::add('edit')
                ->slot('<i class="fas fa-edit"></i> Edit')
                ->class($buttonClass . ' text-blue-600 hover:text-blue-800')
                ->dispatch('edit', ['rowId' => $row->id]),
            Button::add('show')
                ->slot('<i class="fas fa-eye"></i> Show')
                ->class($buttonClass . ' text-green-600 hover:text-green-800')
                ->dispatch('show', ['rowId' => $row->id]),
            Button::add('delete')
                ->slot('<i class="fas fa-trash"></i> Delete')
                ->class($buttonClass . ' text-red-600 hover:text-red-800')
                ->dispatch('delete', ['rowId' => $row->id]),
            Button::add('download')
                ->slot('<i class="fas fa-download"></i> Download Image')
                ->class($buttonClass . ' text-indigo-600 hover:text-indigo-800')
                ->dispatch('download', ['rowId' => $row->id]),
        ];
    }
}
