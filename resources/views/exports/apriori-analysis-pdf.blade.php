<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apriori Analysis Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
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
    </style>
</head>
<body>
    <h1>Apriori Analysis Report</h1>
    
    <h2>Analysis Information</h2>
    <p><strong>Date Range:</strong> {{ $analysis->start_date->format('Y-m-d') }} to {{ $analysis->end_date->format('Y-m-d') }}</p>
    <p><strong>Minimum Support:</strong> {{ $analysis->min_support }}</p>
    <p><strong>Minimum Confidence:</strong> {{ $analysis->min_confidence }}</p>

    <h2>1-Itemset Analysis</h2>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Support</th>
                <th>Transaction Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemsets as $itemset)
            <tr>
                <td>{{ $itemset->product->name }}</td>
                <td>{{ number_format($itemset->support, 4) }}</td>
                <td>{{ $itemset->transaction_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="restock-recommendations">
        <h2>Rekomendasi Restock</h2>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah Transaksi</th>
                    <th>Support</th>
                    <th>Produk Terkait</th>
                    <th>Skor Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis->restockRecommendations as $recommendation)
                    <tr>
                        <td>{{ $recommendation->product->name }}</td>
                        <td>{{ $recommendation->transaction_count }}</td>
                        <td>{{ number_format($recommendation->support, 4) }}</td>
                        <td>{{ implode(', ', array_slice($recommendation->related_products, 0, 3)) }}</td>
                        <td>{{ number_format($recommendation->recommendation_score, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2>Association Rules</h2>
    <table>
        <thead>
            <tr>
                <th>Antecedents</th>
                <th>Consequents</th>
                <th>Support</th>
                <th>Confidence</th>
                <th>Lift</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rules as $rule)
            <tr>
                <td>{{ $rule->antecedents }}</td>
                <td>{{ $rule->consequents }}</td>
                <td>{{ number_format($rule->support, 4) }}</td>
                <td>{{ number_format($rule->confidence, 4) }}</td>
                <td>{{ number_format($rule->lift, 4) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>