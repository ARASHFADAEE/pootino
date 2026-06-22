<?php

namespace Tests\Feature;

use App\Services\SehatsanjiKycService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SehatsanjiKycServiceTest extends TestCase
{
    public function test_verify_identity_succeeds_when_api_returns_valid_result(): void
    {
        config([
            'services.sehatsanji.token' => 'test-token',
            'services.sehatsanji.url' => 'https://sehatsanji.ir/API',
        ]);

        Http::fake([
            'https://sehatsanji.ir/API' => Http::response([
                'result' => [[
                    'shenase' => 'ABC123',
                    'idcode' => '1273835743',
                    'BirthDate' => '1381/07/10',
                    'Validation' => true,
                    'Name' => 'حسین',
                    'Name_Validation' => true,
                    'Family' => 'فدائی جزی',
                    'Family_Validation' => true,
                    'Gender' => 'مرد',
                    'FatherName' => '',
                    'FatherName_Validation' => true,
                    'NationalId' => '1273835743',
                    'NationalId_Validation' => true,
                    'Life' => true,
                ]],
            ]),
        ]);

        $service = app(SehatsanjiKycService::class);
        $result = $service->verifyIdentity(
            idCode: '1273835743',
            birthDate: '1381/07/10',
            name: 'حسین',
            family: 'فدائی جزی',
            nationalId: '1273835743',
        );

        $this->assertTrue($result['ok']);
        $this->assertSame('ABC123', $result['shenase']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://sehatsanji.ir/API'
                && collect($body)->firstWhere('name', 'op')['contents'] === 'IdCode'
                && collect($body)->firstWhere('name', 'IdCode')['contents'] === '1273835743'
                && collect($body)->firstWhere('name', 'BirthDate')['contents'] === '1381/07/10'
                && collect($body)->firstWhere('name', 'Name')['contents'] === 'حسین'
                && collect($body)->firstWhere('name', 'Family')['contents'] === 'فدائی جزی'
                && collect($body)->firstWhere('name', 'NationalId')['contents'] === '1273835743'
                && collect($body)->firstWhere('name', 'token')['contents'] === 'test-token';
        });
    }

    public function test_verify_identity_fails_when_name_validation_is_false(): void
    {
        config(['services.sehatsanji.token' => 'test-token']);

        Http::fake([
            'https://sehatsanji.ir/API' => Http::response([
                'result' => [[
                    'Validation' => true,
                    'Life' => true,
                    'Name_Validation' => false,
                ]],
            ]),
        ]);

        $result = app(SehatsanjiKycService::class)->verifyIdentity(
            idCode: '1273835743',
            birthDate: '1381/07/10',
            name: 'حسین',
        );

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('نام', $result['message']);
    }
}
