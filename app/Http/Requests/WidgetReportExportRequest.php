<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\WidgetReport;
use App\Support\WidgetReportAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WidgetReportExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return WidgetReportAccess::allowed($this->user());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'widget' => [
                'nullable',
                'string',
                Rule::in([
                    WidgetReport::ALL,
                    ...WidgetReport::values(),
                ]),
            ],
            'from_date' => [
                'nullable',
                'date',
            ],
            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],
        ];
    }
}
