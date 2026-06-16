<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Random\RandomException;

if (!function_exists('canAbility')) {
    function canAbility($ability): ?string
    {
        $user = auth()->user();
        if (!$user) return false;

        return $user->can($ability);
    }
}

if (!function_exists('companies')) {
    function companies(?int $companyId = null): array|string|null
    {
        $companies = [
            1 => 'მაილაინი',
            2 => 'თრეიდი',
            3 => 'ქონსტრაქშენი',
        ];

        if (!is_null($companyId)) {
            return $companies[$companyId] ?? null;
        }

        return $companies;
    }
}

if (!function_exists('diffForHumans')) {
    function diffForHumans($date): ?string
    {
        return Carbon::parse($date)->diffForHumans();
    }
}

if (!function_exists('getImageUrl')) {
    function getImageUrl($url = null): ?string
    {
        return empty($url) ? asset('assets/images/no_image.webp') : asset('storage/' . $url);
    }
}

if (!function_exists('dateWithoutTime')) {
    function dateWithoutTime($date): ?string
    {
        return Carbon::parse($date)->format('d-m-Y');
    }
}

if (!function_exists('money')) {
    function money($price): string
    {
        return number_format($price, 2, ',', '.') . ' ₾';
    }
}

if (!function_exists('moneyWithoutSymbol')) {
    function moneyWithoutSymbol($price): string
    {
        return number_format($price, 2, ',', '.');
    }
}


if (!function_exists('send_sms')) {

    function send_sms(string $mobile, string $template, array $params = []): bool
    {
        try {
            $mobile = mobile_format($mobile);

            foreach ($params as $key => $value) {
                $template = str_replace(":{$key}", $value, $template);
            }

            $response = Http::timeout(15)
                ->retry(2, 300)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . config('sms.token'),
                ])
                ->asMultipart()
                ->post('https://smsservice.inexphone.ge/api/v1/sms/one', [
                    [
                        'name'     => 'subject',
                        'contents' => config('sms.subject', 'MYLINE.GE'),
                    ],
                    [
                        'name'     => 'message',
                        'contents' => $template,
                    ],
                    [
                        'name'     => 'phone',
                        'contents' => $mobile,
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('SMS failed', [
                'mobile' => $mobile,
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('SMS exception', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

if (!function_exists('resend_send_sms')) {

    /**
     * @throws RandomException
     */
    function resend_send_sms($mobile): void
    {
        $code = random_int(100000, 999999);

        auth()->user()->update([
            'sms_code' => $code
        ]);

        send_sms($mobile, "Verification code - $code");
    }
}


if (!function_exists('firstUpper')) {
    function firstUpper(string $value): string
    {
        $value = mb_strtolower(trim($value));

        if ($value === '') {
            return $value;
        }

        return mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1);
    }
}


if (!function_exists('mobile_format')) {
    function mobile_format($mobile): string
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);

        if (str_starts_with($mobile, '+995')) {
            $mobile = substr($mobile, 1);
        } elseif (!str_starts_with($mobile, '995')) {
            $mobile = '995' . $mobile;
        }

        return $mobile;
    }
}

if (!function_exists('generateSecurePassword')) {

    function generateSecurePassword(int $length = 12): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $all = $upper . $lower . $digits . $special;

        $password = substr(str_shuffle($upper), 0, 2) .
            substr(str_shuffle($lower), 0, 4) .
            substr(str_shuffle($digits), 0, 2) .
            substr(str_shuffle($special), 0, 2);

        $remaining = $length - strlen($password);
        $password .= substr(str_shuffle($all), 0, $remaining);

        return str_shuffle($password);
    }
}

if (!function_exists('generateOrderId')) {
    function generateOrderId(mixed $order): string
    {

        $prefix = strtoupper('line');

        $lastOrder = $order::query()->orderByDesc('id')
            ->first();

        $lastNumber = 0;

        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->order_id, $matches)) {
            $lastNumber = (int)$matches[1];
        }

        $nextNumber = $lastNumber + 1;

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('toArray')) {
    function toArray(string $model, string $column = 'name', string $key = 'id'): array
    {
        return $model::query()->pluck($column, $key)->toArray();
    }
}

if (!function_exists('parseXfields')) {
    function parseXfields(string $xfields): array
    {
        $result = [];

        // გავყოთ ჩანაწერებად "||"-ით
        foreach (array_filter(explode('||', $xfields), 'strlen') as $pair) {
            // თითო ჩანაწერში პირველი "|" ყოფს key-ს და value-ს
            [$key, $value] = array_pad(explode('|', $pair, 2), 2, null);

            $key = trim((string)$key);
            $value = trim((string)$value);

            if ($key !== '') {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

if (!function_exists('resolveSelectedIdsFromRequest')) {
    /**
     * Resolve selected IDs reliably from Filament request / $records param.
     *
     * @param Collection $records // Filament provided collection (may be partial)
     * @return int[]
     */
    function resolveSelectedIdsFromRequest(Collection $records): array
    {
        $raw = request()->input('records') ?? request()->input('selected') ?? null;

        if (!empty($raw)) {
            if (is_string($raw) && $raw === 'all') {
                return ['all'];
            }

            $arr = (array)$raw;
            $ids = array_map('intval', $arr);
            $ids = array_filter($ids, fn($v) => $v > 0);
            if (!empty($ids)) {
                return array_values($ids);
            }
        }

        $components = request()->input('components') ?? request()->input('components', null);
        if (is_array($components)) {
            foreach ($components as $comp) {
                $snapshotRaw = $comp['snapshot'] ?? $comp['data'] ?? null;
                if (!$snapshotRaw) continue;

                if (is_array($snapshotRaw)) {
                    $snap = $snapshotRaw;
                } else {
                    $decoded = json_decode($snapshotRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $snap = $decoded;
                    } else {
                        continue;
                    }
                }

                $selected = $snap['data']['selectedTableRecords'] ?? null;
                if ($selected && is_array($selected)) {
                    $first = $selected[0] ?? null;
                    if (is_array($first)) {
                        $ids = array_map('intval', $first);
                        $ids = array_filter($ids, fn($v) => $v > 0);
                        if (!empty($ids)) return array_values($ids);
                    } elseif (is_string($first) && trim($first) !== '') {
                        $parts = preg_split('/\s*,\s*/', $first);
                        $ids = array_map('intval', $parts);
                        $ids = array_filter($ids, fn($v) => $v > 0);
                        if (!empty($ids)) return array_values($ids);
                    }
                }
            }
        }

        if ($records->isNotEmpty()) {
            return $records->pluck('id')->map(fn($v) => (int)$v)->all();
        }

        return [];
    }

}
