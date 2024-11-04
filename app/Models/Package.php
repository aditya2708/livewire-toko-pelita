<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'package_price'];

    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class);
    }
    public function packageItems()
    {
        return $this->hasMany(PackageItem::class);
    }
}
