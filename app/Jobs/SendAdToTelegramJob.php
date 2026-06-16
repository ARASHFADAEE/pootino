<?php

namespace App\Jobs;

use App\Models\Ad;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendAdToTelegramJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ad $ad, public bool $isEdit = false) {}

    public function handle(): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.admin_chat_id');

        if (! $token || ! $chatId) {
            return;
        }

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => ($this->isEdit ? 'ویرایش آگهی' : 'آگهی جدید')." #{$this->ad->id}",
        ]);
    }
}
