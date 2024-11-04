<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AprioriAnalysis;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AnalysisDetail extends Component
{
    use AuthorizesRequests;

    public $analysisId;
    public $analysis;
    public $editMode = false;
    public $description;

    protected $rules = [
        'description' => 'nullable|string|max:255',
    ];

    public function mount($analysisId = null)
    {
        $this->analysisId = $analysisId;
        if ($this->analysisId) {
            $this->loadAnalysis();
        }
    }

    public function toggleEditMode()
    {
        $this->editMode = !$this->editMode;
        if (!$this->editMode) {
            $this->description = $this->analysis->description;
        }
    }

    public function updateAnalysis()
    {
        $this->authorize('update', $this->analysis);
        
        $this->validate();

        $this->analysis->update([
            'description' => $this->description,
        ]);

        $this->editMode = false;
        session()->flash('message', 'Analysis description updated successfully.');
    }

    public function loadAnalysis()
    {
        $this->analysis = AprioriAnalysis::with(['itemsets.product', 'restockRecommendations.product', 'rules'])
            ->findOrFail($this->analysisId);
        $this->description = $this->analysis->description;
    }

    public function render()
    {
        if (!$this->analysis) {
            return view('livewire.analysis-detail', ['analysisLoaded' => false]);
        }

        return view('livewire.analysis-detail', [
            'analysisLoaded' => true,
            'itemsets' => $this->analysis->itemsets->sortByDesc('support')->take(10),
            'restockRecommendations' => $this->analysis->restockRecommendations->sortByDesc('recommendation_score')->take(10),
            'rules' => $this->analysis->rules->sortByDesc('lift')->take(20),
        ]);
    }
}