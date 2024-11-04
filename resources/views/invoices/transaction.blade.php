<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Invoice #{{ $transaction->id }}</h1>
    <p>Date: {{ $transaction->transaction_date }}</p>
    <p>Cashier: {{ $transaction->user->name }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
                <tr>
                    <td>{{ $item->is_package ? $item->package->name : $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <p><strong>Total: ${{ number_format($transaction->total_amount, 2) }}</strong></p>
    <p>Paid: ${{ number_format($transaction->paid_amount, 2) }}</p>
    <p>Change: ${{ number_format($transaction->change_amount, 2) }}</p>
</body>
</html>