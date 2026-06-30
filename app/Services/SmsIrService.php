<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsIrService
{
    public function sendOtp(string $mobile, string $code): bool
    {
        $apiKey = config('services.smsir.api_key');
        $templateId = (int) config('services.smsir.template_id');

        if (! $apiKey || ! $templateId) {
            return true;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'text/plain',
            'x-api-key' => $apiKey,
        ])->post('https://api.sms.ir/v1/send/verify', [
            'mobile' => $mobile,
            'templateId' => $templateId,
            'parameters' => [['name' => 'OTP', 'value' => $code]],
        ]);

        return $response->successful();
    }
}
