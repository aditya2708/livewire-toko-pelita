<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'package_id',
        'quantity',
        'unit_price',
        'subtotal',
        'is_package',
    ];

    protected $casts = [
        'is_package' => 'boolean',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function itemDetails()
    {
        return $this->is_package ? $this->package : $this->product;
    }


    // New method to get all individual products, including those in packages
    public function getAllProducts()
    {
        if (!$this->is_package) {
            return $this->product ? collect([
                [
                    'product' => $this->product,
                    'quantity' => $this->quantity
                ]
            ]) : collect();
        }
    
        if (!$this->package) {
            return collect();
        }
    
        return $this->package->items->map(function ($packageItem) {
            return $packageItem->product ? [
                'product' => $packageItem->product,
                'quantity' => $packageItem->quantity * $this->quantity
            ] : null;
        })->filter();
    }
    // New method to get the total quantity of a specific product, including in packages
    public function getTotalProductQuantity($productId)
    {
        if (!$this->is_package) {
            return $this->product_id == $productId ? $this->quantity : 0;
        }

        $packageItem = $this->package->items->firstWhere('product_id', $productId);
        return $packageItem ? $packageItem->quantity * $this->quantity : 0;
    }
}
