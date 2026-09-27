<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        return $product !== null && ($this->user()?->can('update', $product) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');
        $user = $this->user();

        // If user is Editor (and not Admin), they are only allowed to update price, stock, and tags.
        if ($user && $user->isEditor() && ! $user->isAdmin()) {
            return [
                'price' => ['sometimes', 'required', 'numeric', 'min:0'],
                'stock' => ['sometimes', 'required', 'integer', 'min:0'],
                'tags' => ['nullable', 'array'],
                'tags.*' => ['integer', 'exists:tags,id'],
                'name' => ['prohibited'],
                'sku' => ['prohibited'],
                'category_id' => ['prohibited'],
                'description' => ['prohibited'],
                'discount_percentage' => ['prohibited'],
                'rating' => ['prohibited'],
                'thumbnail' => ['prohibited'],
            ];
        }

        // Admin can update all fields
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.required' => 'Harga produk wajib diisi.',
            'price.numeric' => 'Harga produk harus berupa angka.',
            'stock.required' => 'Stok produk wajib diisi.',
            'stock.integer' => 'Stok produk harus berupa bilangan bulat.',
            'name.prohibited' => 'Role editor tidak berhak mengubah nama produk.',
            'sku.prohibited' => 'Role editor tidak berhak mengubah SKU produk.',
            'category_id.prohibited' => 'Role editor tidak berhak mengubah kategori produk.',
            'description.prohibited' => 'Role editor tidak berhak mengubah deskripsi produk.',
            'discount_percentage.prohibited' => 'Role editor tidak berhak mengubah diskon produk.',
            'rating.prohibited' => 'Role editor tidak berhak mengubah rating produk.',
            'thumbnail.prohibited' => 'Role editor tidak berhak mengubah foto produk.',
        ];
    }
}
