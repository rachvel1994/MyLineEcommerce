<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\WidgetReport;
use App\Http\Requests\WidgetReportExportRequest;
use App\Services\Reports\WidgetReportExportService;
use App\Support\WidgetReportAccess;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WidgetReportExportController extends Controller
{
    public function __invoke(
        WidgetReportExportRequest $request,
        WidgetReportExportService $exportService
    ): StreamedResponse {
        abort_unless(WidgetReportAccess::allowed($request->user()), 403);

        $data = $request->validated();
        [$fromDate, $toDate] = $exportService->resolveDateRange(
            $data['from_date'] ?? null,
            $data['to_date'] ?? null,
        );

        return $exportService->downloadResponse(
            $data['widget'] ?? WidgetReport::ALL,
            $fromDate,
            $toDate,
            $request->user(),
        );
    }
}
