<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * Get paginated products with filters and eager loaded relationships.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category:id,name', 'user:id,name,email', 'tags:id,name'])
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when(! empty($filters['category_id']), function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->when(! empty($filters['tag_id']), function ($query) use ($filters) {
                $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag_id']));
            })
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Get a product by ID with relationships.
     */
    public function getProductById(int|string $id): Product
    {
        return Product::query()
            ->with(['category:id,name', 'user:id,name,email', 'tags:id,name'])
            ->findOrFail($id);
    }

    /**
     * Create a new product. Only Admin is authorized.
     *
     * @param  array<string, mixed>  $data
     */
    public function createProduct(array $data, User $user): Product
    {
        return DB::transaction(function () use ($data, $user) {
            $productData = Arr::except($data, ['tags']);
            $productData['user_id'] = $user->id;

            $product = Product::create($productData);

            if (! empty($data['tags'])) {
                $product->tags()->sync($data['tags']);
            }

            return $product->load(['category', 'user:id,name,email', 'tags']);
        });
    }

    /**
     * Update an existing product.
     * Admin can update all fields.
     * Editor can only update numerical quantities (price, stock) and manage/assign tags.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProduct(Product $product, array $data, User $user): Product
    {
        return DB::transaction(function () use ($product, $data, $user) {
            if ($user->isEditor() && ! $user->isAdmin()) {
                // Editor: only allowed to update price, stock, and assign tags
                $allowedAttributes = Arr::only($data, ['price', 'stock']);
                if (! empty($allowedAttributes)) {
                    $product->update($allowedAttributes);
                }
            } else {
                // Admin: can update all product details
                $allowedAttributes = Arr::except($data, ['tags', 'user_id']);
                if (! empty($allowedAttributes)) {
                    $product->update($allowedAttributes);
                }
            }

            // Both Admin and Editor can manage/assign tags
            if (array_key_exists('tags', $data)) {
                $product->tags()->sync($data['tags'] ?? []);
            }

            return $product->fresh(['category', 'user:id,name,email', 'tags']);
        });
    }

    /**
     * Delete a product and its tag associations.
     */
    public function deleteProduct(Product $product, User $user): bool
    {
        return DB::transaction(function () use ($product) {
            $product->tags()->detach();

            return (bool) $product->delete();
        });
    }

    /**
     * Sync/assign tags to a product.
     *
     * @param  array<int>  $tagIds
     */
    public function assignTags(Product $product, array $tagIds): Product
    {
        $product->tags()->sync($tagIds);

        return $product->load('tags');
    }
}
