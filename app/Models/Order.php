<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

#[Fillable(['user_id', 'status', 'total_amount', 'shipping_fee', 'tax_amount', 'shipping_address'])]
class Order extends Model
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
            'status' => OrderStatus::class,
        ];
    }

    /**
     * Get the formatted total amount in Rupiah.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return Number::currency($this->total_amount);
    }

    /**
     * Get the formatted shipping fee in Rupiah.
     */
    public function getFormattedShippingFeeAttribute(): string
    {
        return Number::currency($this->shipping_fee);
    }

    /**
     * Get the customer who placed the order.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this order.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to filter by order status.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeStatus($query, OrderStatus $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope a query to only include delivered orders.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeDelivered($query): void
    {
        $query->where('status', OrderStatus::Delivered);
    }

    /**
     * Scope a query to only include pending orders.
     *
     * @param  Builder<Order>  $query
     */
    public function scopePending($query): void
    {
        $query->where('status', OrderStatus::Pending);
    }
}
