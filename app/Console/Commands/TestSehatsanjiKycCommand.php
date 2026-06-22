<?php

namespace App\Console\Commands;

use App\Services\SehatsanjiKycService;
use Illuminate\Console\Command;

class TestSehatsanjiKycCommand extends Command
{
    protected $signature = 'kyc:sehatsanji-test';

    protected $description = 'تست احراز هویت سحت‌سنجی';

    public function handle(SehatsanjiKycService $kyc): int
    {
        $this->info('در حال ارسال درخواست به سحت‌سنجی...');

        $result = $kyc->verifyIdentity(
            idCode: '1273835743',
            birthDate: '1381/07/10',
            name: 'حسین',
            family: 'فدائی جزی',
            fatherName: 'محمود',
            nationalId: '1273835743',
        );

        if ($result['ok'] ?? false) {
            $this->info('✓ احراز هویت موفق');
            $this->line('شناسه استعلام: '.($result['shenase'] ?? '-'));
            $this->table(
                ['فیلد', 'مقدار'],
                collect($result['data'] ?? [])->map(fn ($v, $k) => [$k, is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE)])->values()->all()
            );

            return self::SUCCESS;
        }

        $this->error('✗ احراز هویت ناموفق');
        $this->line($result['message'] ?? 'خطای نامشخص');

        if (! empty($result['data'])) {
            $this->newLine();
            $this->table(
                ['فیلد', 'مقدار'],
                collect($result['data'])->map(fn ($v, $k) => [$k, is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE)])->values()->all()
            );
        }

        return self::FAILURE;
    }
}
