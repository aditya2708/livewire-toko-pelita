<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\AprioriAnalysis;
use App\Models\AnalysisItemset;
use App\Models\AnalysisRule;

class AprioriAnalysisExport implements WithMultipleSheets
{
    use Exportable;

    protected $analysisId;

    public function __construct($analysisId)
    {
        $this->analysisId = $analysisId;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new class($this->analysisId) implements FromCollection, WithHeadings {
            protected $analysisId;

            public function __construct($analysisId)
            {
                $this->analysisId = $analysisId;
            }

            public function collection()
            {
                return AnalysisItemset::where('apriori_analysis_id', $this->analysisId)
                    ->with('product')
                    ->get()
                    ->map(function ($itemset) {
                        return [
                            $itemset->product->name,
                            $itemset->support,
                            $itemset->transaction_count,
                        ];
                    });
            }

            public function headings(): array
            {
                return [
                    'Product Name',
                    'Support',
                    'Transaction Count',
                ];
            }

            public function title(): string
            {
                return '1-Itemset Analysis';
            }
        };

        $sheets[] = new class($this->analysisId) implements FromCollection, WithHeadings {
            protected $analysisId;

            public function __construct($analysisId)
            {
                $this->analysisId = $analysisId;
            }

            public function collection()
            {
                return AnalysisRule::where('apriori_analysis_id', $this->analysisId)
                    ->get()
                    ->map(function ($rule) {
                        return [
                            $rule->antecedents,
                            $rule->consequents,
                            $rule->support,
                            $rule->confidence,
                            $rule->lift,
                        ];
                    });
            }

            public function headings(): array
            {
                return [
                    'Antecedents',
                    'Consequents',
                    'Support',
                    'Confidence',
                    'Lift',
                ];
            }

            public function title(): string
            {
                return 'Association Rules';
            }
        };

        return $sheets;
    }
}