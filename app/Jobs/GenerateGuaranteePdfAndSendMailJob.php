<?php

namespace App\Jobs;

use App\Mail\OrderNotify;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

class GenerateGuaranteePdfAndSendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $productId)
    {
        $this->onQueue('documents');
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping('guarantee-pdf-' . $this->productId),
        ];
    }

    /**
     * @throws MpdfException
     */
    public function handle(): void
    {
        $product = Product::query()
            ->with([
                'model:id,name',
                'storage:id,name',
                'battery:id,name',
                'information:id,name',
            ])
            ->findOrFail($this->productId);

        $html = view('pdf.guarantee', [
            'product' => $product,
            'date' => $product->created_at?->format('d.m.Y'),
            'totalPrice' => number_format((float) $product->sale_price, 2),
            'fullTitle' => ($product->model?->name ?? '') . ' - ' . ($product?->storage?->name ?? '') . ' - ' . ($product?->battery?->name ?? ''),
            'orderId' => '<div><strong>შეკვეთის ID #:</strong> ' . ($product->order_id ?? '---') . '</div>',
            'showInfo' => $product->show_repair_information
                ? '<div><strong>ინფორმაცია:</strong> ' . $product->information->pluck('name')->join(', ') . '</div>'
                : null,
            'checkedHTML' => $product->need_reset
                ? '<div class="check-note"><span class="check-icon">&#10003;</span> ნივთი დარესეტების შემდეგ საჭიროებს კომპიუტერით აქტივაციას</div>'
                : null,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        $pdfPath = 'guaranties/guarantee-' . $product->sku . '.pdf';

        Storage::disk('local')->put($pdfPath, $mpdf->Output('', 'S'));

        Mail::to('agugesashvili@gmail.com')->send(new OrderNotify($product, $pdfPath));
    }
}
