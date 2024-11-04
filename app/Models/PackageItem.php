<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageItem extends Model
{
    protected $fillable = ['package_id', 'product_id', 'quantity', 'unit_price'];

    /**
     * Get the package that owns the item.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the product for this package item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
