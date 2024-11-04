<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'apriori_analysis_id',
        'report_type',
        'content',
    ];

    public function analysis()
    {
        return $this->belongsTo(AprioriAnalysis::class, 'apriori_analysis_id');
    }
}
