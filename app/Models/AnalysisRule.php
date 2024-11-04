<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'apriori_analysis_id',
        'antecedents',
        'consequents',
        'support',
        'confidence',
        'lift',
        'rule_count'
    ];

    protected $casts = [
        'support' => 'float',
        'confidence' => 'float',
        'lift' => 'float',
    ];

    public function analysis()
    {
        return $this->belongsTo(AprioriAnalysis::class, 'apriori_analysis_id');
    }

    public function getFormattedSupportAttribute()
    {
        $totalTransactions = $this->analysis->total_transactions;
        return sprintf('(%d/%d) * 100%% = %.2f%%', 
            $this->rule_count, 
            $totalTransactions,
            $this->support * 100
        );
    }
}
