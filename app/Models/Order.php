<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
}
