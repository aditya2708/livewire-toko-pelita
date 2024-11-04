<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AprioriAnalysis extends Model
{
    use HasFactory;

    // Define the table associated with this model
    protected $table = 'apriori_analyses';

    // Specify which attributes can be mass-assigned
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'min_support',
        'min_confidence',
        
    ];

    // Define date fields to be cast to Carbon instances
    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    // Define attribute casting for non-string columns
    protected $casts = [
        'min_support' => 'float',
        'min_confidence' => 'float',
      
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the AnalysisRule model
    public function rules()
    {
        return $this->hasMany(AnalysisRule::class);
    }

    // Accessor to format the created_at date
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d M Y H:i');
    }

    // Scope to filter analyses by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Method to get the total number of rules for this analysis
    public function getTotalRulesCount()
    {
        return $this->rules()->count();
    }
}