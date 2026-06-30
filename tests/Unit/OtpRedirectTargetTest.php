<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\OtpController;
use Tests\TestCase;

class OtpRedirectTargetTest extends TestCase
{
    public function test_resolve_redirect_target_accepts_relative_path(): void
    {
        $this->assertSame('/ads/5', $this->resolve('/ads/5'));
    }

    public function test_resolve_redirect_target_extracts_path_from_foreign_host_url(): void
    {
        $this->assertSame('/ads/12', $this->resolve('http://example.test/ads/12'));
    }

    public function test_resolve_redirect_target_rejects_external_paths(): void
    {
        $this->assertNull($this->resolve('https://evil.com/phish'));
    }

    public function test_en_digits_helper_converts_persian_numerals(): void
    {
        $this->assertSame('09123456789', en_digits('۰۹۱۲۳۴۵۶۷۸۹'));
        $this->assertSame('1370/01/01', en_digits('۱۳۷۰/۰۱/۰۱'));
    }

    private function resolve(?string $redirect): ?string
    {
        $controller = app(OtpController::class);
        $method = new \ReflectionMethod($controller, 'resolveRedirectTarget');

        return $method->invoke($controller, $redirect);
    }
}
