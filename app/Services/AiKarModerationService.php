<?php

namespace App\Services;

use App\Models\Ad;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiKarModerationService
{
    public function moderate(Ad $ad): array
    {
        if (app()->environment('local') && config('services.ai_kar.auto_approve_in_local', false)) {
            return ['approved' => true, 'reason' => 'تایید خودکار در محیط توسعه.'];
        }

        $apiKey = (string) config('services.ai_kar.api_key');
        if ($apiKey === '') {
            Log::warning('AI-Kar moderation skipped: API key missing', ['ad_id' => $ad->id]);

            return ['approved' => null, 'reason' => 'سرویس بررسی خودکار پیکربندی نشده است.'];
        }

        $ad->loadMissing(['currentProvince', 'desiredProvince', 'currentBranch']);

        $basePayload = [
            'temperature' => 0.1,
            'max_tokens' => 300,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt($ad),
                ],
            ],
        ];

        $lastError = 'پاسخ نامعتبر از سرویس بررسی خودکار.';

        foreach ($this->modelsToTry() as $model) {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout((int) config('services.ai_kar.timeout', 30))
                    ->connectTimeout(10)
                    ->post(
                        config('services.ai_kar.url', 'https://api.ai-kar.com/v1/chat/completions'),
                        [...$basePayload, 'model' => $model],
                    );
            } catch (\Throwable $e) {
                Log::warning('AI-Kar request exception', [
                    'ad_id' => $ad->id,
                    'model' => $model,
                    'message' => $e->getMessage(),
                ]);

                return ['approved' => null, 'reason' => 'خطا در ارتباط با سرویس بررسی خودکار.'];
            }

            if ($this->isModelNotFound($response)) {
                Log::info('AI-Kar model unavailable, trying fallback', [
                    'ad_id' => $ad->id,
                    'model' => $model,
                    'body' => $response->body(),
                ]);
                $lastError = 'مدل هوش مصنوعی در دسترس نیست.';

                continue;
            }

            if (! $response->ok()) {
                Log::warning('AI-Kar request failed', [
                    'ad_id' => $ad->id,
                    'model' => $model,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['approved' => null, 'reason' => 'پاسخ نامعتبر از سرویس بررسی خودکار.'];
            }

            $content = data_get($response->json(), 'choices.0.message.content');
            if (! is_string($content) || trim($content) === '') {
                return ['approved' => null, 'reason' => 'پاسخ خالی از سرویس بررسی خودکار.'];
            }

            Log::info('AI-Kar moderation succeeded', ['ad_id' => $ad->id, 'model' => $model]);

            return $this->parseDecision($content);
        }

        Log::warning('AI-Kar all models failed', ['ad_id' => $ad->id, 'models' => $this->modelsToTry()]);

        return ['approved' => null, 'reason' => $lastError];
    }

    private function modelsToTry(): array
    {
        $models = array_merge(
            [config('services.ai_kar.model')],
            config('services.ai_kar.fallback_models', []),
        );

        return array_values(array_unique(array_filter($models)));
    }

    private function isModelNotFound(Response $response): bool
    {
        if ($response->status() !== 404) {
            return false;
        }

        $message = (string) data_get($response->json(), 'error.message', $response->body());

        return str_contains(strtolower($message), 'model') && str_contains(strtolower($message), 'not found');
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
تو یک بازبین محتوای پلتفرم «پوتینو» هستی. پوتینو پلتفرم آگهی تبادل محل خدمت سربازی است.

فقط عنوان و توضیحات آگهی را بررسی کن. آگهی را تایید کن اگر:
- مرتبط با درخواست یا پیشنهاد تبادل محل خدمت سربازی باشد
- متن منطقی، محترمانه و قابل فهم باشد
- spam، توهین، شوخی بی‌ربط، متن تصادفی، تبلیغ نامرتبط، یا محتوای خارج از موضوع نباشد

آگهی را رد کن اگر:
- عنوان یا توضیحات بی‌معنا، نامرتبط، آزاردهنده، یا فقط برای اذیت/اسپم نوشته شده
- موضوعی غیر از تبادل محل خدمت سربازی باشد
- متن فارسی/انگلیسی نامفهوم یا پر از حروف تصادفی باشد

فقط JSON معتبر برگردان، بدون markdown:
{"approved": true یا false, "reason": "توضیح کوتاه فارسی برای کاربر"}
PROMPT;
    }

    private function userPrompt(Ad $ad): string
    {
        $org = $ad->currentBranch?->typeLabel() ?? 'نامشخص';
        $description = filled($ad->description) ? $ad->description : '(بدون توضیحات)';

        return <<<TEXT
عنوان: {$ad->title}
توضیحات: {$description}
محل خدمت فعلی: {$ad->currentProvince?->name}
محل درخواستی: {$ad->desiredProvince?->name}
ارگان: {$org}
TEXT;
    }

    private function parseDecision(string $content): array
    {
        $json = json_decode(trim($content), true);

        if (! is_array($json) || ! array_key_exists('approved', $json)) {
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $json = json_decode($matches[0], true);
            }
        }

        if (! is_array($json) || ! array_key_exists('approved', $json)) {
            Log::warning('AI-Kar invalid JSON', ['content' => $content]);

            return ['approved' => null, 'reason' => 'پاسخ نامعتبر از سرویس بررسی خودکار.'];
        }

        $approved = filter_var($json['approved'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($approved === null) {
            return ['approved' => null, 'reason' => 'نتیجه بررسی خودکار نامشخص بود.'];
        }

        $reason = trim((string) ($json['reason'] ?? ''));
        if ($reason === '') {
            $reason = $approved
                ? 'آگهی با موفقیت بررسی و تایید شد.'
                : 'محتوای آگهی با قوانین پلتفرم هم‌خوانی ندارد.';
        }

        return ['approved' => $approved, 'reason' => $reason];
    }
}
