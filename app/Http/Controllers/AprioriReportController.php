<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AprioriAnalysis;

class AprioriReportController extends Controller
{
    public function generateReport($id)
    {
        $analysis = AprioriAnalysis::findOrFail($id);
        // Generate report logic here
        return view('apriori.report', compact('analysis'));
    }

    public function downloadReport($id)
    {
        $analysis = AprioriAnalysis::findOrFail($id);
        // Generate downloadable report logic here
        // For example, you might create a PDF and return it as a download
        // return response()->download($pathToFile);
    }
}
