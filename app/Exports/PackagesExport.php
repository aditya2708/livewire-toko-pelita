<?php

namespace App\Exports;

use App\Models\Package;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PackagesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Package::with('items.product')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Price',
            'Items',
        ];
    }

    public function map($package): array
    {
        return [
            $package->id,
            $package->name,
            $package->description,
            $package->package_price,
            $package->items->map(function ($item) {
                return $item->product->name . ' (x' . $item->quantity . ')';
            })->implode(', '),
        ];
    }
}