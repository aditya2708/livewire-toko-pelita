<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\RestockRecommendation;
use Illuminate\Database\Eloquent\Builder;

class RestockRecommendationTable extends DataTableComponent
{
    public $analysisId;

    public function mount($analysisId)
    {
        $this->analysisId = $analysisId;
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id')
             ->setTableWrapperAttributes([
                'default' => false,
                'class' => 'table-responsive',
             ])
             ->setDefaultSort('recommendation_score', 'desc');
    }

    public function columns(): array
    {
        return [
            Column::make("Product", "product.name")
                  ->sortable()->searchable(),
            Column::make("Transaction Count", "transaction_count")
                  ->sortable(),
            Column::make("Support", "support")
                  ->sortable()
                  ->format(function($value) {
                      return number_format($value, 4);
                  }),
            Column::make("Related Products", "related_products")
                  ->format(function($value) {
                      return implode(', ', array_slice($value, 0, 3));
                  }),
            Column::make("Recommendation Score", "recommendation_score")
                  ->sortable()
                  ->format(function($value) {
                      return number_format($value, 2);
                  }),
        ];
    }

    public function builder(): Builder
    {
        return RestockRecommendation::query()
            ->where('apriori_analysis_id', $this->analysisId)
            ->with('product');
    }
}