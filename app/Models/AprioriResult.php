<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AprioriResult extends Model
{
    use HasFactory;use HasFactory;

    protected $fillable = [
        'analysis_date',
        'min_support',
        'min_confidence',
        'association_rules',
    ];

    protected $casts = [
        'association_rules' => 'array',
    ];
}
