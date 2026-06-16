<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Product::with([
            'user',
            'status',
            'storage',
            'condition',
            'hearAbout',
            'battery',
            'color',
            'guarantee',
            'model',
            'payments.payment',
        ]);

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->input('status_id'));
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        $products = $query
            ->latest('created_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');

        $headers = [
            'A1' => 'თარიღი',
            'B1' => 'სახელი, გვარი',
            'C1' => 'მობილური',
            'D1' => 'პროდუქტი',
            'E1' => 'ასაღები ფასი',
            'F1' => 'გასაყიდი ფასი',
            'G1' => 'რითი მოხდა გადახდა',
            'H1' => 'საიდან გაიგო კლიენტმა',
            'I1' => 'მდგომარეობა',
            'J1' => 'ელემენტი',
            'K1' => 'ფერი',
            'L1' => 'მეხსიერება',
            'M1' => 'გარანტია',
            'N1' => 'IMEI კოდი',
            'O1' => 'კომენტარი',
            'P1' => 'მარაგის სტატუსი',
            'Q1' => 'კომპანია',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $sheet->freezePane('A2');
        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);

        $rowIndex = 2;

        foreach ($products as $product) {
            $payments = $product->payments
                ->sortBy('id')
                ->map(function ($payment) {
                    $name = $payment->payment?->name ?? 'N/A';
                    $price = $payment->price ?? 0;

                    return "{$name} ({$price})";
                })
                ->implode(' - ');

            $productName = $product->model?->name
                ?? $product->name
                ?? $product->title
                ?? '';

            $sheet->setCellValue('A' . $rowIndex, $product->created_at?->format('Y-m-d') ?? '');
            $sheet->setCellValue('B' . $rowIndex, $product->user?->name ?? '');
            $sheet->setCellValue('C' . $rowIndex, $product->user?->mobile ?? '');
            $sheet->setCellValue('D' . $rowIndex, $productName);
            $sheet->setCellValue('E' . $rowIndex, $product->price ?? '');
            $sheet->setCellValue('F' . $rowIndex, $product->sale_price ?? '');
            $sheet->setCellValue('G' . $rowIndex, $payments);
            $sheet->setCellValue('H' . $rowIndex, $product->hearAbout?->name ?? '');
            $sheet->setCellValue('I' . $rowIndex, $product->condition?->name ?? '');
            $sheet->setCellValue('J' . $rowIndex, $product->battery?->name ?? '');
            $sheet->setCellValue('K' . $rowIndex, $product->color?->name ?? '');
            $sheet->setCellValue('L' . $rowIndex, $product->storage?->name ?? '');
            $sheet->setCellValue('M' . $rowIndex, $product->guarantee?->name ?? '');

            $sheet->setCellValueExplicit(
                'N' . $rowIndex,
                (string) ($product->sku ?? ''),
                DataType::TYPE_STRING
            );

            $sheet->setCellValue('O' . $rowIndex, $product->comment ?? '');
            $sheet->setCellValue('P' . $rowIndex, $product->status?->name ?? '');
            $sheet->setCellValue('Q' . $rowIndex, companies($product->company_id));

            $rowIndex++;
        }

        foreach (range('A', 'Q') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="products.xlsx"',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'public',
                'Expires' => '0',
            ]
        );
    }
}