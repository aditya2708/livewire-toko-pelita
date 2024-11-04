<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function generateInvoice($transactionId)
    {
        $transaction = Transaction::with(['items.product', 'items.package', 'user'])->findOrFail($transactionId);
        
        $pdf = PDF::loadView('invoices.transaction', ['transaction' => $transaction]);
        
        return $pdf->download('invoice-' . $transaction->id . '.pdf');
    }
}