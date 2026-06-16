<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, string $secret)
    {
        abort_unless($secret === env('TELEGRAM_WEBHOOK_SECRET'), 403);

        $text = trim(data_get($request->all(), 'message.text', ''));

        if (preg_match('/^\/approve_(\d+)$/', $text, $m)) {
            $ad = Ad::find((int) $m[1]);
            if ($ad) {
                $ad->update(['status' => 'approved', 'approved_at' => now(), 'expires_at' => now()->addDays(30)]);
            }
        }

        if (preg_match('/^\/reject_(\d+)$/', $text, $m)) {
            $ad = Ad::find((int) $m[1]);
            if ($ad) {
                $ad->update(['status' => 'rejected']);
            }
        }

        return response()->json(['ok' => true]);
    }
}
