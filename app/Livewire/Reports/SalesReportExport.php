<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $reportData;
    protected $totalSales;
    protected $totalTransactions;
    protected $averageTransactionValue;
    protected $chartData;

    public function __construct($reportData, $totalSales, $totalTransactions, $averageTransactionValue, $chartData)
    {
        $this->reportData = $reportData;
        $this->totalSales = $totalSales;
        $this->totalTransactions = $totalTransactions;
        $this->averageTransactionValue = $averageTransactionValue;
        $this->chartData = $chartData;
    }

    public function collection()
    {
        // Add summary data at the beginning
        $summaryData = new Collection([
            ['Summary'],
            ['Total Sales', number_format($this->totalSales, 2)],
            ['Total Transactions', $this->totalTransactions],
            ['Average Transaction Value', number_format($this->averageTransactionValue, 2)],
            [], // Empty row for spacing
            ['Date', 'Sales'], // Headers for chart data
        ]);

        // Add chart data
        foreach ($this->chartData as $data) {
            $summaryData->push([$data['date'], $data['total']]);
        }

        $summaryData->push([]); // Empty row for spacing
        $summaryData->push(['Detailed Transactions']); // Header for detailed transactions

        // Combine summary data with report data
        return $summaryData->concat($this->reportData);
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Date',
            'Customer',
            'Total Amount',
            'Items'
        ];
    }

    public function map($row): array
    {
        // Check if this is a transaction row (object) or a summary row (array)
        if (is_object($row)) {
            return [
                $row->id,
                $row->transaction_date->format('Y-m-d H:i'),
                $row->user->name,
                number_format($row->total_amount, 2),
                $row->items->pluck('product.name')->implode(', ')
            ];
        }
        
        // Return the row as is for summary data
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:E1' => ['font' => ['bold' => true]],
            'A' => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 30,
            'D' => 15,
            'E' => 50,
        ];
    }

    public function title(): string
    {
        return 'Sales Report';
    }
}