<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'user_id',
        'total',
        'status',
        'processing_start',
        'ready_at',
        'delivery_start',
        'delivered_at',
        'delivery_address'
    ];

    protected $casts = [
        'processing_start' => 'datetime',
        'ready_at' => 'datetime',
        'delivery_start' => 'datetime',
        'delivered_at' => 'datetime',
        'total' => 'decimal:2'
    ];

    protected $with = ['items', 'customer'];

    /**
     * The attributes that should be cast to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($sale) {
            if (!$sale->code) {
                $sale->code = 'SALE-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the customer who made the sale.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the store where the sale was made.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the items for this sale.
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function addItem($product, $quantity, $unit_price)
    {
        $total = $unit_price * $quantity;
        
        $item = $this->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $unit_price,
            'subtotal' => $total
        ]);

        $this->updateTotal();
        
        return $item;
    }

    public function updateTotal()
    {
        $total = $this->items()->sum('subtotal');
        $this->update(['total' => $total]);
        return $total;
    }

    public function markAsPaid()
    {
        return $this->update([
            'status' => 'paid'
        ]);
    }

    public function markAsCancelled()
    {
        return $this->update([
            'status' => 'cancelled'
        ]);
    }
}
