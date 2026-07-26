<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Database\DatabaseExportService;
use App\Support\DatabaseExportAccess;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseExportController extends Controller
{
    public function __invoke(
        Request $request,
        DatabaseExportService $exporter,
    ): BinaryFileResponse {
        abort_unless(DatabaseExportAccess::allowed($request->user()), 403);

        $path = $exporter->export();

        return response()
            ->download($path, basename($path), [
                'Cache-Control' => 'no-store, private',
                'Content-Type' => 'application/sql',
                'Pragma' => 'no-cache',
            ])
            ->deleteFileAfterSend();
    }
}
