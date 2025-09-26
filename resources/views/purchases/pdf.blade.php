<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Receipt - {{ $purchase->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .details {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $purchase->store->name ?? 'POSKU' }}</h1>
        <p>Purchase Receipt</p>
        <p>Invoice: {{ $purchase->invoice_number }}</p>
        <p>Date: {{ $purchase->transaction_date->format('d/m/Y H:i') }}</p>
    </div>

    <div class="details">
        <p><strong>User:</strong> {{ $purchase->user->name }}</p>
        @if ($purchase->supplier)
            <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->purchaseDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>Rp {{ number_format($detail->price_at_sale, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total">Total</td>
                <td class="total">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
            </tr>
            @if ($purchase->discount > 0)
                <tr>
                    <td colspan="3">Discount</td>
                    <td>Rp {{ number_format($purchase->discount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="3" class="total">Final Amount</td>
                <td class="total">Rp {{ number_format($purchase->final_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3">Paid</td>
                <td>Rp {{ number_format($purchase->amount_paid, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3">Change</td>
                <td>Rp {{ number_format($purchase->change_due, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if ($purchase->notes)
        <p><strong>Notes:</strong> {{ $purchase->notes }}</p>
    @endif
</body>

</html>
