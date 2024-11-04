<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        h1, h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary-item {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Transaksi</h1>
        <p>Periode: {{ $startDate }} to {{ $endDate }}</p>

        <div class="summary">
           
            <div class="summary-item">
                <strong>Total Penjualan:</strong> Rp{{ number_format($totalSales, 0, ',', '.') }}
            </div>
            <div class="summary-item">
                <strong>Total Transaksi:</strong> {{ $totalTransactions }}
            </div>
            <div class="summary-item">
                <strong>Rata - Rata penjualan:</strong> Rp{{ number_format($averageTransactionValue, 0, ',', '.') }}
            </div>
        </div>

        <h2>Top 10 Produk</h2>
        <table>
            <thead>
                <tr>
                    <th>Ranking</th>
                    <th>Nama Produk</th>
                    <th>Total Terjual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['total_sold'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Transaksi</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                
                    <th>Total </th>
                    <th>Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date->isoFormat('D MMMM Y') }}</td>
                       
                        <td>Rp{{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                        <td>
                            <ul>
                                @foreach($transaction->items as $item)
                                    <li>
                                        @if($item->is_package)
                                            Paket: {{ $item->package->name }} ({{ $item->quantity }})
                                            <ul>
                                                @foreach($item->package->items as $packageItem)
                                                    <li>{{ $packageItem->product->name }} ({{ $packageItem->quantity * $item->quantity }})</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ $item->product->name }} ({{ $item->quantity }})
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>