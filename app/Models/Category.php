<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    
    public function scopeSearch($query, $searchTerm)
    {
        if ($searchTerm) {
            return $query->where('name', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('description', 'LIKE', "%{$searchTerm}%");
        }
        return $query;
    }

    public function hasProducts()
{
    return $this->products()->exists();
}
}
