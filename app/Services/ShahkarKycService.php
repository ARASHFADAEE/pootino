<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShahkarKycService
{
    public function verifyPhoneNationalCode(string $phone, string $nationalCode): array
    {
        if (app()->environment('local') && ! config('services.shahkar.enforce_in_local', false)) {
            return ['ok' => true];
        }

        $clientId = (string) config('services.finnotech.client_id');
        $token = (string) config('services.finnotech.token');
        $baseUrl = rtrim((string) config('services.finnotech.address', 'https://sandboxapi.finnotech.ir'), '/');

        if (! $clientId || ! $token) {
            Log::warning('Shahkar KYC skipped: Finnotech credentials missing');

            return app()->environment('local')
                ? ['ok' => true]
                : ['ok' => false, 'message' => 'سرویس تطبیق شماره موبایل و کد ملی پیکربندی نشده است.'];
        }

        try {
            $response = Http::withToken($token)
                ->timeout((int) config('services.shahkar.timeout', 20))
                ->connectTimeout(10)
                ->post("{$baseUrl}/facility/v2/clients/{$clientId}/shahkar", [
                    'mobile' => $phone,
                    'nationalCode' => $nationalCode,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Shahkar request exception', ['message' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'خطا در ارتباط با سرویس تطبیق هویت. لطفاً دوباره تلاش کنید.'];
        }

        if (! $response->ok()) {
            Log::warning('Shahkar request failed', ['status' => $response->status(), 'body' => $response->body()]);

            return ['ok' => false, 'message' => 'خطا در ارتباط با سرویس تطبیق هویت.'];
        }

        $body = $response->json();
        if (! is_array($body)) {
            return ['ok' => false, 'message' => 'پاسخ نامعتبر از سرویس تطبیق هویت.'];
        }

        $isMatched = data_get($body, 'result.isValid')
            ?? data_get($body, 'result')
            ?? data_get($body, 'responseCode') === 'SUCCESS';

        if ($this->isTruthy($isMatched)) {
            return ['ok' => true, 'data' => $body];
        }

        return [
            'ok' => false,
            'message' => 'شماره تلفن یا مشخصات شما با اطلاعات هویتی شما منطبق نمی‌باشد.',
        ];
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'done', 'success'], true);
    }
}
