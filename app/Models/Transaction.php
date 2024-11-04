<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'paid_amount',
        'change_amount',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($query) use ($term) {
            $query->where('id', 'like', "%{$term}%")
                  ->orWhere('total_amount', 'like', "%{$term}%")
                  ->orWhereHas('user', function ($query) use ($term) {
                      $query->where('name', 'like', "%{$term}%");
                  });
        });
    }
}
