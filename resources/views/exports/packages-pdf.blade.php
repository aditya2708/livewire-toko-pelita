<!-- resources/views/exports/packages-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Packages Report</title>
    <style>
        /* Add some basic styling */
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 5px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Packages Report</h1>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $package)
                <tr>
                    <td>{{ $package->name }}</td>
                    <td>{{ $package->description }}</td>
                    <td>${{ number_format($package->package_price, 2) }}</td>
                    <td>
                        @foreach($package->items as $item)
                            {{ $item->product->name }} (x{{ $item->quantity }})<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>