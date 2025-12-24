<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rencana Pembelian - {{ $plan->doc_no }}</title>
    <style>
        :root {
            --border: #222;
            --light: #f0f0f0;
        }

        body {
            font-family: Tahoma, Arial, sans-serif;
            color: #111;
            margin: 18px;
            font-size: 12px;
        }

        .sheet {
            border: 2px solid var(--border);
            padding: 16px 18px 20px;
        }

        .header-top {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border);
        }

        .header-left p,
        .header-right p {
            margin: 0 0 6px;
        }

        .header-right {
            text-align: right;
            min-width: 220px;
        }

        .section-title {
            font-weight: 700;
            margin: 12px 0 8px;
            text-transform: uppercase;
            font-size: 13px;
        }

        .info-grid {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 10px;
        }

        .info-block p {
            margin: 0 0 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid var(--border);
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #d9d9d9;
            font-weight: 700;
            text-transform: none;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 12px;
            margin-top: 12px;
        }

        .terms {
            border: 1px solid var(--border);
            padding: 10px 12px;
            min-height: 180px;
        }

        .terms ul {
            margin: 6px 0 0 18px;
            padding: 0;
        }

        .summary {
            border: 1px solid var(--border);
            padding: 10px 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .signature {
            border: 1px solid var(--border);
            padding: 18px 12px 12px;
            margin-top: 10px;
            text-align: center;
            min-height: 120px;
        }

        @media print {
            body {
                margin: 8mm;
            }
        }
    </style>
</head>

<body>
    <div class="sheet">
        <div class="header-top">
            {{ $plan->store?->name ?? 'TOKO' }}
        </div>

        <div class="header-row">
            <div class="header-left">
                <p>{{ $plan->store?->address ?? '-' }}</p>
                <p>{{ $plan->store?->phone ?? '-' }}</p>
                <p>{{ $plan->store?->email ?? '-' }}</p>
            </div>
            <div class="header-right">
                <p style="font-size: 18px; font-weight: 700;">PURCHASE ORDER</p>
                <p>{{ $plan->plan_date->format('d M Y') }}</p>
                <p><strong>PURCHASE ORDER NO: {{ $plan->doc_no }}</strong></p>
            </div>
        </div>

        <div class="section-title">Informasi Supplier</div>
        <div class="info-grid">
            <div class="info-block">
                <p><strong>{{ $plan->supplier?->name ?? '-' }}</strong></p>
                <p>{{ $plan->supplier?->address ?? '-' }}</p>
                <p>{{ $plan->supplier?->phone ?? '-' }}</p>
            </div>
            <div class="info-block text-right">
                <p><strong>CONTACT PERSON</strong></p>
                <p>{{ $plan->supplier?->contact_person ?? '-' }}</p>
                <p>Staff Purchasing</p>
            </div>
        </div>

        @php
            $isPkp = (bool) ($plan->supplier?->is_pkp ?? false);
            $totalGross = 0;
            $totalTax = 0;
            $totalFinal = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 6%;">No.</th>
                    <th>Item</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-center" style="width: 8%;">Unit</th>
                    <th class="text-right" style="width: 14%;">Harga (Rp)</th>
                    <th class="text-right" style="width: 14%;">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($plan->items as $item)
                    @php
                        $lineNo = $loop->iteration;
                        $qty = (int) ($item->planned_qty ?? 0);
                        $price = (float) ($item->product?->purchase_price ?? 0);
                        $gross = $qty * $price;
                        $taxRate = (float) ($item->product?->tax_rate ?? 0);
                        $taxable = $isPkp && $item->product?->tax_type === 'Y';
                        $tax = $taxable ? ($gross * $taxRate) / 100 : 0;
                        $total = $gross + $tax;

                        $totalGross += $gross;
                        $totalTax += $tax;
                        $totalFinal += $total;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $lineNo }}</td>
                        <td>
                            <div><strong>{{ $item->product?->name ?? '-' }}</strong></div>
                            <div style="font-size: 11px;">{{ $item->product?->product_code ?? '-' }}</div>
                        </td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-center">{{ $item->product?->unit ?? '-' }}</td>
                        <td class="text-right">{{ number_format($price, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada item.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-grid">
            <div>
                <div class="summary">
                    <div class="summary-row">
                        <span>Sub total</span>
                        <strong>{{ number_format($totalGross, 0, ',', '.') }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>PPN {{ $isPkp ? (int) ($plan->items->first()?->product?->tax_rate ?? 0) : 0 }}%</span>
                        <span>{{ number_format($totalTax, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row" style="font-weight: 700;">
                        <span>Total</span>
                        <span>{{ number_format($totalFinal, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="signature">
                    <div>Disetujui oleh,</div>
                    <div style="margin-top: 54px;">{{ $plan->creator?->name ?? '-' }}</div>
                    <div>Supervisor/Manager Purchasing</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
