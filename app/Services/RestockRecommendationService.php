<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RestockRecommendation;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RestockRecommendationService
{
    public function generateRecommendations($aprioriResults)
    {
        $products = Product::all();

        foreach ($products as $product) {
            $frequency = $this->getFrequencyInRules($product->id, $aprioriResults);
            $confidence = $this->getHighestConfidence($product->id, $aprioriResults);
            $currentStock = $product->stock_quantity;
            $monthlySales = $this->getProductMonthlySales($product->id);

            $score = $this->calculateScore($frequency, $confidence, $currentStock, $monthlySales);
            $recommendation = $this->getRecommendation($score);

            RestockRecommendation::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'frequency' => $frequency,
                    'confidence' => $confidence,
                    'current_stock' => $currentStock,
                    'monthly_sales' => $monthlySales,
                    'score' => $score,
                    'recommendation' => $recommendation,
                ]
            );
        }
    }

    private function getFrequencyInRules($productId, $aprioriResults)
    {
        $frequency = 0;
        foreach ($aprioriResults as $rule) {
            if (in_array($productId, $rule['antecedent']) || in_array($productId, $rule['consequent'])) {
                $frequency++;
            }
        }
        return $frequency;
    }

    private function getHighestConfidence($productId, $aprioriResults)
    {
        $highestConfidence = 0;
        foreach ($aprioriResults as $rule) {
            if ((in_array($productId, $rule['antecedent']) || in_array($productId, $rule['consequent'])) &&
                $rule['confidence'] > $highestConfidence) {
                $highestConfidence = $rule['confidence'];
            }
        }
        return $highestConfidence;
    }

    private function getProductMonthlySales($productId)
    {
        $oneMonthAgo = Carbon::now()->subMonth();
        return TransactionItem::where('product_id', $productId)
            ->whereHas('transaction', function ($query) use ($oneMonthAgo) {
                $query->where('transaction_date', '>=', $oneMonthAgo);
            })
            ->sum('quantity');
    }

    private function calculateScore($frequency, $confidence, $currentStock, $monthlySales)
    {
        $normalizedFrequency = min($frequency / 20, 1);
        $stockRatio = $monthlySales > 0 ? $currentStock / $monthlySales : 1;
        $score = ($normalizedFrequency * 0.3) + ($confidence * 0.3) + 
                 (max(0, 1 - $stockRatio) * 0.4);
        return round($score * 10, 1);
    }

    private function getRecommendation($score)
    {
        if ($score >= 8) {
            return 'Segera';
        } elseif ($score >= 6) {
            return 'Pertimbangkan';
        } else {
            return 'Monitor';
        }
    }
}