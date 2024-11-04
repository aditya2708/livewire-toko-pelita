<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisItemset extends Model
{
    use HasFactory;

    protected $fillable = [
        'apriori_analysis_id',
        'product_id',
        'support',
        'transaction_count'
    ];

    protected $casts = [
        'support' => 'float',
        'transaction_count' => 'integer',
    ];

    /**
     * Get the Apriori analysis that this itemset belongs to.
     */
    public function aprioriAnalysis()
    {
        return $this->belongsTo(AprioriAnalysis::class);
    }

    /**
     * Get the product associated with this itemset.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
