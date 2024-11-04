<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class Sales extends Component
{
    public function render()
    {
        return view('livewire.reports.sales')->layout('layouts.app');;
    }
}
