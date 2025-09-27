<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcodes</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .print-container {
                display: block !important;
            }

            .no-print {
                display: none !important;
            }
        }

        @media screen {
            .print-container {
                display: none;
            }
        }

        .barcode-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5cm;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: white;
            min-height: 2cm;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="no-print text-center py-4 bg-gray-100">
        <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded">
            Print Barcode
        </button>
    </div>

    <div class="print-container">
        @php
            $products = $products ?? [];
            $layout = $layout ?? ['rows' => 5, 'columns' => 3];
            $totalPages = ceil(count($products) / ($layout['rows'] * $layout['columns']));
        @endphp

        @for ($page = 1; $page <= $totalPages; $page++)
            <div class="page-break"
                style="page-break-after: always; width: 100%; height: 100vh; display: grid; grid-template-rows: repeat({{ $layout['rows'] }}, 1fr); gap: 0.5cm; padding: 0.5cm; box-sizing: border-box;">
                @for ($row = 1; $row <= $layout['rows']; $row++)
                    <div
                        style="display: grid; grid-template-columns: repeat({{ $layout['columns'] }}, 1fr); gap: 0.5cm; height: 100%;">
                        @for ($col = 1; $col <= $layout['columns']; $col++)
                            @php
                                $index =
                                    ($page - 1) * ($layout['rows'] * $layout['columns']) +
                                    ($row - 1) * $layout['columns'] +
                                    ($col - 1);
                                $product = $products[$index] ?? null;
                            @endphp
                            <div class="barcode-item">
                                @if ($product && is_array($product))
                                    <div style="text-align: center; margin-bottom: 0.2cm;">
                                        @if ($product['barcode_image'] ?? null)
                                            <img src="{{ $product['barcode_image'] }}"
                                                alt="Barcode {{ $product['product_code'] ?? 'N/A' }}"
                                                style="max-width: 100%; height: 1.5cm; object-fit: contain;" />
                                        @endif
                                    </div>
                                    <div
                                        style="font-size: 8px; font-family: monospace; text-align: center; margin-bottom: 0.1cm;">
                                        {{ $product['product_code'] ?? 'N/A' }}
                                    </div>
                                    <div
                                        style="font-size: 6px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                        {{ $product['name'] ?? 'N/A' }}
                                    </div>
                                @endif
                            </div>
                        @endfor
                    </div>
                @endfor
            </div>
        @endfor
    </div>

    <script>
        // Auto print when page loads
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>

</html>
