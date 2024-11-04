<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'apriori_analysis_id',
        'product_id',
        'transaction_count',
        'support',
        'related_products',
        'recommendation_score',
    ];

    protected $casts = [
        'support' => 'float',
        'recommendation_score' => 'float',
        'related_products' => 'array',
    ];

    public function aprioriAnalysis()
    {
        return $this->belongsTo(AprioriAnalysis::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}