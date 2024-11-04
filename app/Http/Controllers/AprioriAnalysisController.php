<?php

namespace App\Http\Controllers;

use App\Models\AprioriAnalysis;
use App\Helpers\AprioriHelper;

class AprioriAnalysisController extends Controller
{
    public function show(AprioriAnalysis $analysis)
    {
        $analysis->load('rules');
        
        AprioriHelper::initialize($analysis->rules->toArray());

        if (!AprioriHelper::isInitialized()) {
            // Handle case where initialization failed
            return view('analysis-detail', ['error' => 'Tidak dapat menginisialisasi analisis']);
        }

        $topRuleWithExamples = $analysis->getTopRuleWithExamples();

        return view('analysis-detail', compact('analysis', 'topRuleWithExamples'));
    }
}