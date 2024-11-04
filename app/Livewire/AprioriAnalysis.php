<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\AprioriAnalysis as AprioriAnalysisModel;
use App\Models\AnalysisRule;
use App\Models\AnalysisItemset;
use App\Models\RestockRecommendation;
use Phpml\Association\Apriori;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AprioriAnalysis extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $minSupport = 0.1;
    public $minConfidence = 0.5;
    public $maxRules = 100;

    public $aprioriAnalysisId;
    public $isAnalysisComplete = false;
    public $showSaveButton = false;
    public $errorMessage = '';
    public $successMessage = '';

    protected $rules = [
        'startDate' => 'required|date|before:endDate',
        'endDate' => 'required|date|after:startDate',
        'minSupport' => 'required|numeric|between:0.01,1',
        'minConfidence' => 'required|numeric|between:0.1,1',
        'maxRules' => 'required|integer|between:1,1000',
    ];

    public function mount()
    {
        $this->startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function updatedStartDate()
    {
        $this->validateOnly('startDate');
    }

    public function updatedEndDate()
    {
        $this->validateOnly('endDate');
    }

    public function runAnalysis()
    {
        $this->validate();

        $this->isAnalysisComplete = false;
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->showSaveButton = false;

        try {
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate)->endOfDay();
            
            $transactions = $this->getTransactions($startDate, $endDate);
            
            if ($transactions->isEmpty()) {
                throw new \Exception("No transactions found in the selected date range.");
            }

            $samples = $this->prepareSamples($transactions);
            $itemsets = $this->calculateItemsets($samples);
            $associationRules = $this->performAprioriAnalysis($samples);

            if (empty($associationRules)) {
                throw new \Exception("No rules found. Try adjusting the minimum support and confidence values.");
            }

            $formattedRules = $this->formatResults($associationRules);
            $restockRecommendations = $this->calculateRestockRecommendations($itemsets, $formattedRules);

            $this->saveAnalysisResults($itemsets, $formattedRules, $restockRecommendations);

            $this->isAnalysisComplete = true;
            $this->showSaveButton = false; // We're saving automatically now
            $this->successMessage = 'Analysis completed and saved successfully. ' . count($formattedRules) . ' rules found.';
            
        } catch (\Exception $e) {
            Log::error('Apriori Analysis Error: ' . $e->getMessage());
            $this->errorMessage = "An error occurred: " . $e->getMessage();
        }
    }

    private function getTransactions($startDate, $endDate)
    {
        return Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->with('items.product', 'items.package.items.product')
            ->get();
    }

    private function prepareSamples($transactions)
    {
        return $transactions->map(function ($transaction) {
            $items = collect();
            foreach ($transaction->items as $item) {
                if ($item->is_package) {
                    $items = $items->concat($item->package->items->pluck('product.id'));
                } else {
                    $items->push($item->product_id);
                }
            }
            return $items->unique()->values()->toArray();
        })->filter()->values()->toArray();
    }

    private function calculateItemsets($samples)
    {
        $totalTransactions = count($samples);
        $itemCounts = [];

        foreach ($samples as $sample) {
            foreach ($sample as $item) {
                if (!isset($itemCounts[$item])) {
                    $itemCounts[$item] = 0;
                }
                $itemCounts[$item]++;
            }
        }

        $products = Product::whereIn('id', array_keys($itemCounts))->pluck('name', 'id');

        return collect($itemCounts)->map(function ($count, $itemId) use ($totalTransactions, $products) {
            $support = $count / $totalTransactions;
            return [
                'product_id' => $itemId,
                'product_name' => $products[$itemId] ?? 'Unknown Product',
                'support' => $support,
                'transaction_count' => $count,
            ];
        })->sortByDesc('support')->values()->toArray();
    }

    private function performAprioriAnalysis($samples)
    {
        $apriori = new Apriori($this->minSupport, $this->minConfidence);
        $apriori->train($samples, []);
        return $apriori->getRules();
    }

    private function formatResults($rules)
    {
        return collect($rules)->map(function ($rule) {
            $antecedents = $this->getProductNames($rule['antecedent']);
            $consequents = $this->getProductNames($rule['consequent']);
            
            return [
                'antecedents' => $antecedents,
                'consequents' => $consequents,
                'support' => $rule['support'],
                'confidence' => $rule['confidence'],
                'lift' => $this->calculateLift($rule),
            ];
        })->sortByDesc('support')->values()->take($this->maxRules)->toArray();
    }

    private function getProductNames($productIds)
    {
        return Product::whereIn('id', $productIds)->pluck('name')->implode(', ');
    }

    private function calculateLift($rule)
    {
        // This is a simplified calculation and might need adjustment based on your specific requirements
        return $rule['confidence'] / $rule['support'];
    }

    private function calculateRestockRecommendations($itemsets, $rules)
    {
        return collect($itemsets)->map(function ($itemset) use ($rules) {
            $relatedProducts = $this->getRelatedProducts($itemset['product_id'], $rules);
            $recommendationScore = $this->calculateRecommendationScore($itemset, $relatedProducts);
            
            return [
                'product_id' => $itemset['product_id'],
                'product_name' => $itemset['product_name'],
                'transaction_count' => $itemset['transaction_count'],
                'support' => $itemset['support'],
                'related_products' => $relatedProducts,
                'recommendation_score' => $recommendationScore,
            ];
        })->sortByDesc('recommendation_score')->values()->take(10)->toArray();
    }

    private function getRelatedProducts($productId, $rules)
    {
        return collect($rules)
            ->filter(function ($rule) use ($productId) {
                return strpos($rule['antecedents'], (string)$productId) !== false ||
                       strpos($rule['consequents'], (string)$productId) !== false;
            })
            ->pluck('antecedents')
            ->merge(collect($rules)->pluck('consequents'))
            ->flatten()
            ->unique()
            ->diff([$productId])
            ->values()
            ->toArray();
    }

    private function calculateRecommendationScore($itemset, $relatedProducts)
    {
        $baseScore = $itemset['support'] * 100;
        $relatedScore = count($relatedProducts) * 5;
        return $baseScore + $relatedScore;
    }

    private function saveAnalysisResults($itemsets, $rules, $restockRecommendations)
    {
        DB::beginTransaction();

        try {
            $analysis = AprioriAnalysisModel::create([
                'user_id' => auth()->id(),
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'min_support' => $this->minSupport,
                'min_confidence' => $this->minConfidence,
                'max_rules' => $this->maxRules,
            ]);

            $this->aprioriAnalysisId = $analysis->id;

            foreach ($itemsets as $itemset) {
                AnalysisItemset::create([
                    'apriori_analysis_id' => $analysis->id,
                    'product_id' => $itemset['product_id'],
                    'support' => $itemset['support'],
                    'transaction_count' => $itemset['transaction_count'],
                ]);
            }

            foreach ($rules as $rule) {
                AnalysisRule::create([
                    'apriori_analysis_id' => $analysis->id,
                    'antecedents' => $rule['antecedents'],
                    'consequents' => $rule['consequents'],
                    'support' => $rule['support'],
                    'confidence' => $rule['confidence'],
                    'lift' => $rule['lift'],
                ]);
            }

            foreach ($restockRecommendations as $recommendation) {
                RestockRecommendation::create([
                    'apriori_analysis_id' => $analysis->id,
                    'product_id' => $recommendation['product_id'],
                    'transaction_count' => $recommendation['transaction_count'],
                    'support' => $recommendation['support'],
                    'related_products' => $recommendation['related_products'],
                    'recommendation_score' => $recommendation['recommendation_score'],
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving analysis: ' . $e->getMessage());
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.apriori-analysis');
    }
}