<?php

namespace App\Console\Commands;

use App\Mail\DailyReportMail;
use App\Models\Product;
use App\Models\Status;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class GenerateDailySalesReport extends Command
{
    protected $signature = 'report:daily-sales';

    protected $description = 'Generate daily sales report and send email';

    public function handle(): int
    {
        $today = now();

        $products = Product::query()
            ->with(['model', 'storage'])
            ->where('status_id', 4)
            ->whereDate('created_at', $today)
            ->get();

        $totalRevenue = $products->sum('sale_price');
        $totalCost = $products->sum('price');
        $profit = $totalRevenue - $totalCost;

        $html = view('pdf.daily-report', [
            'products' => $products,
            'date' => $today->format('d.m.Y'),
            'totalRevenue' => number_format($totalRevenue, 2),
            'totalCost' => number_format($totalCost, 2),
            'profit' => number_format($profit, 2),
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        $fileName = 'reports/daily-sales-' . $today->format('Y-m-d') . '.pdf';

        Storage::disk('local')->put($fileName, $mpdf->Output('', 'S'));

        Mail::to('agugesashvili@gmail.com')
            ->send(new DailyReportMail($fileName));

        $this->info('Daily report generated and sent successfully.');

        return self::SUCCESS;
    }
}
