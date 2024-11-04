<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class Inventory extends Component
{
    public function render()
    {
        return view('livewire.reports.inventory')->layout('layouts.app');
    }
}
