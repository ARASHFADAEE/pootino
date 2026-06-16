<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShahkarKycService
{
    public function verifyMobileNationalCode(string $mobile, string $nationalCode): array
    {
        $hour = now()->setTimezone('Asia/Tehran')->hour;
        if ($hour >= 23 || $hour < 7) {
            return ['ok' => false, 'message' => 'سرویس تطبیق شاهکار از ساعت 23 تا 7 صبح در دسترس نیست.'];
        }

        $address = rtrim((string) config('services.finnotech.address'), '/');
        $clientId = (string) config('services.finnotech.client_id');
        $token = (string) config('services.finnotech.token');

        if (! $address || ! $clientId || ! $token) {
            return ['ok' => false, 'message' => 'تنظیمات سرویس احراز هویت شاهکار کامل نیست.'];
        }

        $trackId = (string) Str::uuid();

        $response = Http::withToken($token)->get("{$address}/kyc/v2/clients/{$clientId}/shahkar/smsSend", [
            'trackId' => $trackId,
            'mobile' => $mobile,
            'nationalCode' => $nationalCode,
            'version' => 2,
        ]);

        if (! $response->ok()) {
            Log::warning('Shahkar request failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['ok' => false, 'message' => 'خطا در ارتباط با سرویس شاهکار.'];
        }

        $payload = $response->json();
        $smsSent = (bool) data_get($payload, 'result.smsSent', false);

        if (! $smsSent) {
            $message = data_get($payload, 'error.message') ?: data_get($payload, 'responseCode') ?: 'شماره موبایل و کد ملی منطبق نیست.';
            return ['ok' => false, 'message' => $message];
        }

        return ['ok' => true, 'track_id' => data_get($payload, 'trackId', $trackId)];
    }
}
