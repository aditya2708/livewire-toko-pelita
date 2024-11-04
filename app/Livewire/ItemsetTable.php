<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\AnalysisItemset;
use Illuminate\Database\Eloquent\Builder;

class ItemsetTable extends DataTableComponent
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
             ->setDefaultSort('support', 'desc');
    }

    public function columns(): array
    {
        return [
            Column::make("Product", "product.name")
                  ->sortable()->searchable(),
            Column::make("Support", "support")
                  ->sortable()
                  ->format(function($value) {
                      return number_format($value, 4);
                  }),
            Column::make("Transaction Count", "transaction_count")
                  ->sortable(),
        ];
    }

    public function builder(): Builder
    {
        return AnalysisItemset::query()
            ->where('apriori_analysis_id', $this->analysisId)
            ->with('product');
    }
}