<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SehatsanjiKycService
{
    public function verifyIdentity(
        string $idCode,
        string $birthDate,
        ?string $name = null,
        ?string $family = null,
        ?string $fatherName = null,
        ?string $nationalId = null,
    ): array {
        $token = (string) config('services.sehatsanji.token');
        $baseUrl = rtrim((string) config('services.sehatsanji.url', 'https://sehatsanji.ir/API'), '/');

        if (! $token) {
            return ['ok' => false, 'message' => 'تنظیمات سرویس احراز هویت (سحت‌سنجی) کامل نیست.'];
        }

        $payload = array_filter([
            'IdCode' => $idCode,
            'BirthDate' => $birthDate,
            'Name' => $name,
            'Family' => $family,
            'FatherName' => $fatherName,
            'NationalId' => $nationalId,
            'token' => $token,
            'op' => 'IdCode',
        ], fn ($value) => $value !== null && $value !== '');

        try {
            $multipart = collect($payload)
                ->map(fn ($value, $key) => ['name' => $key, 'contents' => (string) $value])
                ->values()
                ->all();

            $response = Http::asMultipart()
                ->timeout((int) config('services.sehatsanji.timeout', 30))
                ->connectTimeout(10)
                ->post($baseUrl, $multipart);
        } catch (\Throwable $e) {
            Log::warning('Sehatsanji request exception', ['message' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'خطا در ارتباط با سرویس سحت‌سنجی. لطفاً دوباره تلاش کنید.'];
        }

        if (! $response->ok()) {
            Log::warning('Sehatsanji request failed', ['status' => $response->status(), 'body' => $response->body()]);

            return ['ok' => false, 'message' => 'خطا در ارتباط با سرویس سحت‌سنجی.'];
        }

        $body = $response->json();
        if (! is_array($body)) {
            Log::warning('Sehatsanji invalid JSON', ['body' => $response->body()]);

            return ['ok' => false, 'message' => 'پاسخ نامعتبر از سرویس سحت‌سنجی.'];
        }

        $result = data_get($body, 'result.0');
        if (! is_array($result)) {
            $message = data_get($body, 'message') ?: data_get($body, 'error') ?: 'اطلاعات هویتی تأیید نشد.';

            return ['ok' => false, 'message' => is_string($message) ? $message : 'اطلاعات هویتی تأیید نشد.'];
        }

        if (! $this->isTruthy($result['Validation'] ?? false)) {
            return ['ok' => false, 'field' => 'birth_date', 'message' => 'کد ملی و تاریخ تولد با یکدیگر مطابقت ندارند.', 'data' => $result];
        }

        if (! $this->isTruthy($result['Life'] ?? true)) {
            return ['ok' => false, 'message' => 'وضعیت حیات در سامانه تأیید نشد.'];
        }

        if ($name !== null && $name !== '' && ! $this->isTruthy($result['Name_Validation'] ?? false)) {
            return ['ok' => false, 'field' => 'first_name', 'message' => 'نام با اطلاعات ثبت‌شده مطابقت ندارد.', 'data' => $result];
        }

        if ($family !== null && $family !== '' && ! $this->isTruthy($result['Family_Validation'] ?? false)) {
            return ['ok' => false, 'field' => 'family', 'message' => 'نام خانوادگی با اطلاعات ثبت‌شده مطابقت ندارد.', 'data' => $result];
        }

        if ($fatherName !== null && $fatherName !== '' && ! $this->isTruthy($result['FatherName_Validation'] ?? false)) {
            return ['ok' => false, 'field' => 'father_name', 'message' => 'نام پدر با اطلاعات ثبت‌شده مطابقت ندارد.', 'data' => $result];
        }

        if ($nationalId !== null && $nationalId !== '' && ! $this->isTruthy($result['NationalId_Validation'] ?? false)) {
            return ['ok' => false, 'field' => 'national_id', 'message' => 'شماره شناسنامه با اطلاعات ثبت‌شده مطابقت ندارد.', 'data' => $result];
        }

        return [
            'ok' => true,
            'shenase' => $result['shenase'] ?? null,
            'data' => $result,
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

        return in_array($normalized, ['1', 'true', 'yes', 'y'], true);
    }
}
