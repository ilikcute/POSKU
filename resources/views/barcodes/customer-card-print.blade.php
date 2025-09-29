<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Customer Cards</title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.5cm;
            }

            body {
                margin: 0;
                padding: 0;
                background: linear-gradient(to bottom, #0c1445 0%, #1e3a8a 30%, #ff6b35 70%, #f7931e 100%);
                background-attachment: fixed;
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

        .customer-card-item {
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
            Print Customer Cards
        </button>
    </div>

    <div class="print-container">
        @php
            $customers = $customers ?? [];
            $layout = $layout ?? ['rows' => 5, 'columns' => 2];
            $totalPages = ceil(count($customers) / ($layout['rows'] * $layout['columns']));
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
                                $customer = $customers[$index] ?? null;
                            @endphp
                            <div class="customer-card-item">
                                @if ($customer && is_array($customer))
                                    <div
                                        style="border: 2px solid #ddd; border-radius: 8px; padding: 8px; background: white; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: space-between; font-family: Arial, sans-serif;">
                                        <!-- Photo -->
                                        @if ($customer['photo_url'] ?? null)
                                            <div style="margin-bottom: 4px;">
                                                <img src="{{ asset('storage/' . $customer['photo_url']) }}"
                                                    alt="Photo {{ $customer['name'] ?? '' }}"
                                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc;" />
                                            </div>
                                        @endif

                                        <!-- Customer Code -->
                                        <div
                                            style="font-size: 10px; font-weight: bold; margin-bottom: 2px; color: #333;">
                                            {{ $customer['customer_code'] ?? 'N/A' }}
                                        </div>

                                        <!-- Name -->
                                        <div
                                            style="font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #000; line-height: 1.2; max-height: 30px; overflow: hidden;">
                                            {{ $customer['name'] ?? 'N/A' }}
                                        </div>

                                        <!-- Customer Type -->
                                        <div style="font-size: 9px; margin-bottom: 2px; color: #555;">
                                            <strong>Jenis:</strong> {{ $customer['customer_type'] ?? 'N/A' }}
                                        </div>

                                        <!-- Status -->
                                        <div
                                            style="font-size: 9px; margin-bottom: 2px; color: {{ ($customer['status'] ?? '') === 'active' ? '#28a745' : '#dc3545' }};">
                                            <strong>Status:</strong> {{ ucfirst($customer['status'] ?? 'N/A') }}
                                        </div>

                                        <!-- Joined Date -->
                                        <div style="font-size: 9px; margin-bottom: 4px; color: #666;">
                                            <strong>Bergabung:</strong> {{ $customer['joined_at'] ?? 'N/A' }}
                                        </div>

                                        <!-- QR Code -->
                                        @if ($customer['qr_code_image'] ?? null)
                                            <div style="margin-top: auto; padding-top: 4px;">
                                                <img src="{{ $customer['qr_code_image'] }}"
                                                    alt="QR Code {{ $customer['customer_code'] ?? '' }}"
                                                    style="width: 60px; height: 60px; object-fit: contain;" />
                                            </div>
                                        @endif
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
