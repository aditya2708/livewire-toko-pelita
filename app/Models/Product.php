<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'unit_price',
        'stock_quantity',
        'category_id',
        'barcode',
        'photo_path',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo_path
            ? Storage::url($this->photo_path)
            : 'https://via.placeholder.com/150';
    }

    public static function boot()
{
    parent::boot();

    static::creating(function ($product) {
        $product->barcode = static::generateUniqueBarcode();
    });
}

public static function generateUniqueBarcode()
{
    do {
        $barcode = strtoupper(Str::random(6));
    } while (static::where('barcode', $barcode)->exists());

    return $barcode;
}

public function packageItems()
{
    return $this->hasMany(PackageItem::class);
}

public function calculateMonthlySales()
{
    $lastMonth = Carbon::now()->subMonth();
    
    return DB::table('transaction_items')
        ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
        ->where('transaction_items.product_id', $this->id)
        ->where('transactions.transaction_date', '>=', $lastMonth)
        ->sum('transaction_items.quantity');
}

public function getMonthlySales()
    {
        $oneMonthAgo = Carbon::now()->subMonth();
        return $this->transactionItems()
            ->whereHas('transaction', function ($query) use ($oneMonthAgo) {
                $query->where('transaction_date', '>=', $oneMonthAgo);
            })
            ->sum('quantity');
    }
}
