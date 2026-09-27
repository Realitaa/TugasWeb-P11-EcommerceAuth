<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

#[Fillable(['category_id', 'user_id', 'name', 'sku', 'price', 'stock', 'description', 'discount_percentage', 'rating', 'thumbnail'])]
class Product extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
        ];
    }

    /**
     * Get the final price after discount.
     */
    public function getFinalPriceAttribute(): float
    {
        if ($this->discount_percentage > 0) {
            return (float) ($this->price - ($this->price * ($this->discount_percentage / 100)));
        }

        return (float) $this->price;
    }

    /**
     * Get the formatted price in Rupiah.
     */
    public function getFormattedPriceAttribute(): string
    {
        return Number::currency($this->price);
    }

    /**
     * Get the formatted final price after discount in Rupiah.
     */
    public function getFormattedFinalPriceAttribute(): string
    {
        return Number::currency($this->final_price);
    }

    /**
     * Get the category that the product belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user (admin/editor) who created the product.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tags associated with the product.
     *
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags')->withTimestamps();
    }

    /**
     * Get the order items containing this product.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to only include in-stock products.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeInStock($query): void
    {
        $query->where('stock', '>', 0);
    }

    /**
     * Scope a query to only include products with active discounts.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeDiscounted($query): void
    {
        $query->where('discount_percentage', '>', 0);
    }

    /**
     * Scope a query to filter products by minimum rating.
     *
     * @param  Builder<Product>  $query
     */
    public function scopePopular($query, float $minRating = 4.0): void
    {
        $query->where('rating', '>=', $minRating);
    }
}
