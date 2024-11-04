<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\AnalysisRule;
use Illuminate\Database\Eloquent\Builder;

class AssociationRuleTable extends DataTableComponent
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
            Column::make("Produk Dibeli Bersama", "antecedents")
                  ->sortable()->searchable(),
            Column::make("Biasanya Juga Beli", "consequents")
                  ->sortable()->searchable(),
            Column::make("Seberapa Sering", "support")
                  ->sortable()
                  ->format(function($value) {
                      return number_format($value, 4);
                  }),
            Column::make("Tingkat Kemungkinan", "confidence")
                  ->sortable()
                  ->format(function($value) {
                      return number_format($value, 4);
                  }),
            Column::make("Kekuatan Hubungan", "lift")
                  ->sortable()
                  ->format(function($value) {
                      return number_format($value, 4);
                  }),
        ];
    }

    public function builder(): Builder
    {
        return AnalysisRule::query()
            ->where('apriori_analysis_id', $this->analysisId);
    }
}