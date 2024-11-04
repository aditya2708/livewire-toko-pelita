@php
use App\Helpers\AprioriHelper;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Analisis Apriori</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Hasil Analisis Apriori</h1>
    <p>Tanggal Analisis: {{ $analysis->created_at->format('d M Y H:i') }}</p>
    <p>Periode: {{ Carbon\Carbon::parse($analysis->start_date)->format('d M Y') }} sampai {{ Carbon\Carbon::parse($analysis->end_date)->format('d M Y') }}</p>


    <table>
        <thead>
            <tr>
                <th>Produk Yang Sering Dibeli Bersama</th>
                <th>Biasanya Juga Membeli</th>
                <th>Seberapa Sering Terjadi</th>
                <th>Tingkat Kemungkinan</th>
                <th>Kekuatan Hubungan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rules as $rule)
                <tr>
                    <td>{{ $rule->antecedents }}</td>
                    <td>{{ $rule->consequents }}</td>
                    <td>{{ \App\Helpers\AprioriHelper::getSupportDescription($rule->support) }}</td>
                    <td>{{ \App\Helpers\AprioriHelper::getConfidenceDescription($rule->confidence) }}</td>
                    <td>{{ \App\Helpers\AprioriHelper::getLiftDescription($rule->lift) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>