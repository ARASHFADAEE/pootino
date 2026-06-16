<?php

namespace App\Console\Commands;

use App\Models\Ad;
use Illuminate\Console\Command;

class ExpireAds extends Command
{
    protected $signature = 'ads:expire';

    protected $description = 'غیرفعال کردن آگهی‌های منقضی‌شده';

    public function handle(): int
    {
        $count = Ad::where('status', 'approved')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);

        $this->info("{$count} آگهی منقضی شد.");
        return self::SUCCESS;
    }
}
