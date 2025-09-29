<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Picqer\Barcode\BarcodeGeneratorSVG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/Barcodes/Index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'product_codes' => 'required|array|min:1',
            'product_codes.*' => 'required|string|exists:products,product_code',
            'barcode_type' => 'required|in:code128,qrcode',
            'rows' => 'required|integer|min:1|max:20',
            'columns' => 'required|integer|min:1|max:10',
            'paper_size' => 'required|in:A4',
        ]);

        $products = Product::whereIn('product_code', $request->product_codes)->get();

        $barcodeData = [];
        $generator = new BarcodeGeneratorSVG();

        foreach ($products as $product) {
            $barcodeImage = '';

            if ($request->barcode_type === 'qrcode') {
                // Generate QR code using SimpleSoftwareIO with SVG format
                try {
                    $barcodeImage = 'data:image/svg+xml;base64,' . base64_encode(
                        QrCode::size(150)->format('svg')->generate($product->product_code)
                    );
                } catch (\Exception $e) {
                    $barcodeImage = '';
                }
            } else {
                // Generate barcode using SVG (no image extensions needed)
                try {
                    $barcodeImage = 'data:image/svg+xml;base64,' . base64_encode(
                        $generator->getBarcode($product->product_code, $generator::TYPE_CODE_128)
                    );
                } catch (\Exception $e) {
                    $barcodeImage = '';
                }
            }

            $barcodeData[] = [
                'product_code' => $product->product_code,
                'name' => $product->name,
                'barcode_image' => $barcodeImage,
            ];
        }

        return response()->json([
            'barcode_data' => $barcodeData,
            'layout' => [
                'rows' => $request->rows,
                'columns' => $request->columns,
                'paper_size' => $request->paper_size,
            ]
        ]);
    }

    public function print(Request $request)
    {
        $product_codes = json_decode($request->input('product_codes'), true);
        $request->merge(['product_codes' => $product_codes]);

        $request->validate([
            'product_codes' => 'required|array|min:1',
            'product_codes.*' => 'required|string|exists:products,product_code',
            'barcode_type' => 'required|in:code128,qrcode',
            'rows' => 'required|integer|min:1|max:20',
            'columns' => 'required|integer|min:1|max:10',
            'paper_size' => 'required|in:A4',
        ]);

        $products = Product::whereIn('product_code', $product_codes)->get();

        // Generate barcode images for each product using SVG
        $generator = new BarcodeGeneratorSVG();
        $productsWithBarcodes = collect($products)->map(function ($product) use ($request, $generator) {
            $barcodeImage = '';

            if ($request->barcode_type === 'qrcode') {
                // Generate QR code using SimpleSoftwareIO with SVG format
                try {
                    $barcodeImage = 'data:image/svg+xml;base64,' . base64_encode(
                        QrCode::size(150)->format('svg')->generate($product->product_code)
                    );
                } catch (\Exception $e) {
                    $barcodeImage = '';
                }
            } else {
                // Generate barcode using SVG
                try {
                    $barcodeImage = 'data:image/svg+xml;base64,' . base64_encode(
                        $generator->getBarcode($product->product_code, $generator::TYPE_CODE_128)
                    );
                } catch (\Exception $e) {
                    $barcodeImage = '';
                }
            }

            return [
                'product_code' => $product->product_code,
                'name' => $product->name,
                'barcode_image' => $barcodeImage,
            ];
        });

        return response()->view('barcodes.print', [
            'products' => $productsWithBarcodes->toArray(),
            'layout' => [
                'rows' => $request->rows,
                'columns' => $request->columns,
                'paper_size' => $request->paper_size,
            ]
        ])->header('Content-Type', 'text/html');
    }

    public function generatePriceTags(Request $request)
    {
        $request->validate([
            'product_codes' => 'required|array|min:1',
            'product_codes.*' => 'required|string|exists:products,product_code',
            'rows' => 'required|integer|min:1|max:20',
            'columns' => 'required|integer|min:1|max:10',
            'paper_size' => 'required|in:A4',
        ]);

        $products = Product::with(['promotions' => function ($query) {
            $query->active();
        }])->whereIn('product_code', $request->product_codes)->get();

        $priceTagData = $products->map(function ($product) {
            $priceInfo = $product->getPriceWithPromotion();

            $activePromotion = $product->promotions->first();

            return [
                'product_code' => $product->product_code,
                'name' => $product->name,
                'original_price' => $priceInfo['original_price'],
                'final_price' => $priceInfo['final_price'],
                'discount_amount' => $priceInfo['discount_amount'],
                'has_promotion' => $priceInfo['discount_amount'] > 0,
                'promotion_name' => $priceInfo['promotion_name'],
                'promotion_end_date' => $activePromotion ? $activePromotion->end_date->format('d/m/Y') : null,
                'printed_at' => now()->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'price_tag_data' => $priceTagData,
            'layout' => [
                'rows' => $request->rows,
                'columns' => $request->columns,
                'paper_size' => $request->paper_size,
            ]
        ]);
    }

    public function printPriceTags(Request $request)
    {
        $product_codes = json_decode($request->input('product_codes'), true);
        $request->merge(['product_codes' => $product_codes]);

        $request->validate([
            'product_codes' => 'required|array|min:1',
            'product_codes.*' => 'required|string|exists:products,product_code',
            'rows' => 'required|integer|min:1|max:20',
            'columns' => 'required|integer|min:1|max:10',
            'paper_size' => 'required|in:A4',
        ]);

        $products = Product::with(['promotions' => function ($query) {
            $query->active();
        }])->whereIn('product_code', $product_codes)->get();

        $productsWithPriceTags = collect($products)->map(function ($product) {
            $priceInfo = $product->getPriceWithPromotion();

            $activePromotion = $product->promotions->first();

            return [
                'product_code' => $product->product_code,
                'name' => $product->name,
                'original_price' => $priceInfo['original_price'],
                'final_price' => $priceInfo['final_price'],
                'has_promotion' => $priceInfo['discount_amount'] > 0,
                'promotion_name' => $priceInfo['promotion_name'],
                'promotion_end_date' => $activePromotion ? $activePromotion->end_date->format('d/m/Y') : null,
                'printed_at' => now()->format('d/m/Y H:i'),
            ];
        });

        return response()->view('barcodes.price-tag-print', [
            'products' => $productsWithPriceTags->toArray(),
            'layout' => [
                'rows' => $request->rows,
                'columns' => $request->columns,
                'paper_size' => $request->paper_size,
            ]
        ])->header('Content-Type', 'text/html');
    }

    public function generateCustomerCards(Request $request)
    {
        $request->validate([
            'customer_codes' => 'required|array|min:1',
            'customer_codes.*' => 'required|string|exists:customers,customer_code',
            'rows' => 'required|integer|min:1|max:20',
            'columns' => 'required|integer|min:1|max:10',
            'paper_size' => 'required|in:A4',
        ]);

        $customers = Customer::with('customerType')->whereIn('customer_code', $request->customer_codes)->get();

        $customerCardData = $customers->map(function ($customer) {
            $qrCodeImage = '';
            try {
                $qrCodeImage = 'data:image/svg+xml;base64,' . base64_encode(
                    QrCode::size(100)->format('svg')->generate($customer->customer_code)
                );
            } catch (\Exception $e) {
                $qrCodeImage = '';
            }

            return [
                'customer_code' => $customer->customer_code,
                'name' => $customer->name,
                'status' => $customer->status ?? ($customer->is_active ? 'active' : 'inactive'),
                'customer_type' => $customer->customerType ? $customer->customerType->name : 'N/A',
                'joined_at' => $customer->joined_at ? $customer->joined_at->format('d/m/Y') : 'N/A',
                'photo_url' => $customer->photo_url,
                'qr_code_image' => $qrCodeImage,
            ];
        });

        return response()->json([
            'customer_card_data' => $customerCardData,
            'layout' => [
                'rows' => $request->rows,
                'columns' => $request->columns,
                'paper_size' => $request->paper_size,
            ]
        ]);
    }

    public function printCustomerCards(Request $request)
    {
        $customer_codes = json_decode($request->input('customer_codes'), true);
        $request->merge(['customer_codes' => $customer_codes]);

        $request->validate([
            'customer_codes' => 'required|array|min:1',
            'customer_codes.*' => 'required|string|exists:customers,customer_code',
            'rows' => 'required|integer|min:1|max:20',
            'columns' => 'required|integer|min:1|max:10',
            'paper_size' => 'required|in:A4',
        ]);

        $customers = Customer::with('customerType')->whereIn('customer_code', $customer_codes)->get();

        $customersWithCards = collect($customers)->map(function ($customer) {
            $qrCodeImage = '';
            try {
                $qrCodeImage = 'data:image/svg+xml;base64,' . base64_encode(
                    QrCode::size(100)->format('svg')->generate($customer->customer_code)
                );
            } catch (\Exception $e) {
                $qrCodeImage = '';
            }

            return [
                'customer_code' => $customer->customer_code,
                'name' => $customer->name,
                'status' => $customer->status ?? ($customer->is_active ? 'active' : 'inactive'),
                'customer_type' => $customer->customerType ? $customer->customerType->name : 'N/A',
                'joined_at' => $customer->joined_at ? $customer->joined_at->format('d/m/Y') : 'N/A',
                'photo_url' => $customer->photo_url,
                'qr_code_image' => $qrCodeImage,
            ];
        });

        return response()->view('barcodes.customer-card-print', [
            'customers' => $customersWithCards->toArray(),
            'layout' => [
                'rows' => $request->rows,
                'columns' => $request->columns,
                'paper_size' => $request->paper_size,
            ]
        ])->header('Content-Type', 'text/html');
    }
}
