<?php

namespace App\Jobs;

use App\Models\Ad;
use App\Services\AiKarModerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ModerateAdJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ad $ad) {}

    public function handle(AiKarModerationService $moderation): void
    {
        $ad = $this->ad->fresh();

        if (! $ad || $ad->status !== 'pending') {
            return;
        }

        $result = $moderation->moderate($ad);
        $approved = $result['approved'] ?? null;
        $reason = $result['reason'] ?? '';

        if ($approved === true) {
            $ad->update([
                'status' => 'approved',
                'is_active' => true,
                'approved_at' => now(),
                'expires_at' => now()->addDays(30),
                'admin_note' => null,
                'edited_after_approval' => false,
            ]);

            return;
        }

        if ($approved === false) {
            $ad->update([
                'status' => 'rejected',
                'is_active' => false,
                'admin_note' => 'رد خودکار: '.$reason,
            ]);

            return;
        }

        $ad->update([
            'admin_note' => 'در انتظار بررسی دستی: '.$reason,
        ]);
    }
}
