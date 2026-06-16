<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

class PdfController extends Controller
{
    /**
     * @throws MpdfException
     */
    public function guaranteePdf(Request $request, int $id): Response|ResponseFactory
    {
        $lang = $request->query('lang', app()->getLocale());

        if (! in_array($lang, ['ka', 'en', 'ru'], true)) {
            $lang = 'ka';
        }

        app()->setLocale($lang);

        $product = Product::with([
            'user',
            'guarantee',
            'information',
            'storage',
            'battery',
        ])->findOrFail($id);

        $html = view('pdf.guarantee', [
            'product' => $product,
            'lang' => $lang,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        $filename = "guarantee-{$product->id}-{$lang}.pdf";

        return response($mpdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}