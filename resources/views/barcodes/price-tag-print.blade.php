<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Price Tags</title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.5in;
            }

            body {
                margin: 0;
                padding: 0;
                font-family: 'Tahoma', sans-serif;
            }

            .print-container {
                width: 100%;
                height: 100vh;
                background: white;
                page-break-inside: avoid;
            }

            .price-tag-item {
                page-break-inside: avoid;
                box-sizing: border-box;
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
    </style>
</head>

<body>
    <div class="no-print text-center py-4 bg-gray-100">
        <button onclick="window.print()" class="bg-green-500 text-white px-4 py-2 rounded">
            Print Price Tag
        </button>
    </div>

    <div class="print-container"
        style="display: grid; grid-template-columns: repeat({{ $layout['columns'] ?? 3 }}, 1fr); grid-template-rows: repeat({{ $layout['rows'] ?? 5 }}, 1fr); gap: 2px;">
        @php
            $totalPositions = ($layout['rows'] ?? 5) * ($layout['columns'] ?? 3);
        @endphp

        @for ($position = 0; $position < $totalPositions; $position++)
            @php
                $product = ($products ?? [])[$position] ?? null;
            @endphp
            <div class="price-tag-item"
                style="display: flex; align-items: center; justify-content: space-between; padding: 8px; border: 1px solid #000; background: white; box-sizing: border-box;">
                @if ($product && is_array($product))
                    <div class="product-info" style="flex: 1; text-align: left;">
                        <div class="product-code"
                            style="font-family: 'Tahoma', sans-serif; font-size: 10px; font-weight: bold; margin-bottom: 2px;">
                            {{ $product['product_code'] ?? 'N/A' }}
                        </div>
                        <div class="product-name"
                            style="font-family: 'Tahoma', sans-serif; font-size: 9px; line-height: 1.1; margin-bottom: 4px; max-height: 20px; overflow: hidden;">
                            {{ $product['name'] ?? 'N/A' }}
                        </div>
                        @if (($product['has_promotion'] ?? false) && ($product['promotion_name'] ?? null))
                            <div class="promotion"
                                style="font-family: 'Tahoma', sans-serif; font-size: 8px; color: red; margin-bottom: 1px;">
                                PROMO: {{ $product['promotion_name'] }}
                            </div>
                            @if ($product['promotion_end_date'] ?? null)
                                <div class="promotion-end"
                                    style="font-family: 'Tahoma', sans-serif; font-size: 8px; color: red;">
                                    Sampai: {{ $product['promotion_end_date'] }}
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="price-section" style="text-align: right; flex-shrink: 0;">
                        @if (($product['has_promotion'] ?? false) && ($product['original_price'] ?? null))
                            <div class="original-price"
                                style="font-family: 'Tahoma', sans-serif; font-size: 10px; text-decoration: line-through; color: gray; margin-bottom: 2px;">
                                {{ is_numeric($product['original_price']) ? number_format($product['original_price'], 0, ',', '.') : 'N/A' }}
                            </div>
                        @endif
                        <div class="final-price"
                            style="font-family: 'Tahoma', sans-serif; font-size: 16px; font-weight: bold; color: blue;">
                            {{ is_numeric($product['final_price'] ?? null) ? number_format($product['final_price'], 0, ',', '.') : 'N/A' }}
                        </div>
                    </div>
                    <div class="printed-at"
                        style="font-family: 'Tahoma', sans-serif; font-size: 8px; color: gray; margin-top: 2px; text-align: center; width: 100%;">
                        {{ $product['printed_at'] ?? now()->format('d/m/Y H:i') }}
                    </div>
                @endif
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
