<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AprioriAnalysis;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class SavedAnalyses extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $confirmingDeletion = false;
    public $analysisToDelete;
   

    protected $queryString = ['dateFrom', 'dateTo', 'sortField', 'sortDirection'];

    public function mount()
    {
        $this->dateFrom = Carbon::now()->subMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function confirmDelete($analysisId)
    {
        $this->analysisToDelete = $analysisId;
        $this->confirmingDeletion = true;
    }

    public function deleteAnalysis()
    {
        $analysis = AprioriAnalysis::findOrFail($this->analysisToDelete);
        $analysis->delete();

        $this->confirmingDeletion = false;
        $this->analysisToDelete = null;

        session()->flash('message', 'Analysis deleted successfully.');
        $this->js('window.location.reload()');
    }

    public function generatePDF($id)
    {
        $analysis = AprioriAnalysis::with('rules')->findOrFail($id);

        $data = [
            'analysis' => $analysis,
            'rules' => $analysis->rules,
        ];

        $pdf = PDF::loadView('exports.saved-analysis-pdf', $data);
        
        return response()->streamDownload(
            function() use ($pdf) {
                echo $pdf->output();
            },
            'apriori_analysis_' . $analysis->id . '_' . now()->format('Y-m-d') . '.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="apriori_analysis_' . $analysis->id . '_' . now()->format('Y-m-d') . '.pdf"'
            ]
        );
    }

    public function applyDateFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $analyses = AprioriAnalysis::whereBetween('created_at', [$this->dateFrom, Carbon::parse($this->dateTo)->endOfDay()])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.saved-analyses', [
            'analyses' => $analyses,
        ]);
    }
}