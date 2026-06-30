<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\SehatsanjiKycService;
use App\Services\ShahkarKycService;
use App\Services\SmsIrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    public function __construct(
        protected SmsIrService $sms,
        protected SehatsanjiKycService $sehatsanji,
        protected ShahkarKycService $shahkar,
    ) {}

    public function showPhoneForm(Request $request)
    {
        $this->rememberRedirectTarget(
            $request->query('redirect') ?? session('url.intended')
        );

        return view('auth.phone');
    }
    public function showVerifyForm() { return view('auth.verify'); }
    public function showCompleteProfileForm() { return view('auth.complete-profile'); }
    public function showVerificationRequired() { return view('auth.verification-required'); }

    public function sendOtp(Request $request)
    {
        $this->rememberRedirectTarget(session('post_auth_redirect') ?? session('url.intended'));

        $request->merge(['phone' => en_digits($request->input('phone'))]);

        $request->validate([
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
        ]);

        $phone = $request->string('phone')->toString();
        $isLocalEnv = app()->environment('local');

        $key = 'otp:'.$phone;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return back()->withInput()->withErrors(['phone' => 'لطفا 2 دقیقه دیگر تلاش کنید.']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Otp::where('phone', $phone)->where('used', false)->delete();
        Otp::create(['phone' => $phone, 'code' => $code, 'expires_at' => now()->addMinutes(2)]);
        if (! $isLocalEnv) {
            $this->sms->sendOtp($phone, $code);
        }
        RateLimiter::hit($key, 120);

        session([
            'otp_phone' => $phone,
            'otp_preview_code' => $isLocalEnv ? $code : null,
        ]);

        return redirect()->route('auth.otp.verify-form');
    }

    public function verifyOtp(Request $request)
    {
        $request->merge(['code' => en_digits($request->input('code'))]);

        $request->validate(['code' => ['required', 'digits:6']]);

        $phone = session('otp_phone');
        $otp = Otp::where('phone', $phone)->where('code', $request->code)->where('used', false)->where('expires_at', '>', now())->latest()->first();

        if (! $otp) {
            return back()->withErrors(['code' => 'کد نادرست یا منقضی شده است.']);
        }

        $otp->update(['used' => true]);
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'کاربر']
        );

        $savedRedirect = session('post_auth_redirect') ?? session('url.intended');

        Auth::login($user);
        session()->forget(['otp_phone', 'otp_preview_code']);

        if ($savedRedirect) {
            $this->rememberRedirectTarget($savedRedirect);
        }

        if (! $user->national_code) {
            return redirect()->route('auth.otp.complete-profile-form')->with('success', 'برای تکمیل ثبت‌نام، اطلاعات هویتی را وارد کن.');
        }

        return $this->redirectAfterAuth()->with('success', 'خوش آمدید');
    }

    public function resendOtp(Request $request)
    {
        $request->merge([
            'phone' => session('otp_phone'),
        ]);

        return $this->sendOtp($request);
    }

    public function completeProfile(Request $request)
    {
        $request->merge([
            'national_code' => en_digits($request->input('national_code')),
            'birth_date' => en_digits($request->input('birth_date')),
        ]);

        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'family' => ['required', 'string', 'max:50'],
            'father_name' => ['required', 'string', 'max:50'],
            'national_code' => ['required', 'digits:10', function ($attribute, $value, $fail) {
                if (! $this->isValidIranianNationalCode($value)) {
                    $fail('کد ملی معتبر نیست.');
                }
            }],
            'birth_date' => ['required', 'regex:/^\d{4}\/\d{2}\/\d{2}$/', function ($attribute, $value, $fail) {
                if (! is_valid_jalali_birth_age($value)) {
                    $fail('سن باید بین ۱۸ تا ۱۲۰ سال باشد.');
                }
            }],
        ], [
            'birth_date.regex' => 'تاریخ تولد باید به فرمت 1381/07/10 باشد.',
        ]);

        /** @var User $user */
        $user = $request->user();
        $nationalCode = $request->string('national_code')->toString();

        $shahkar = $this->shahkar->verifyPhoneNationalCode($user->phone, $nationalCode);
        if (! ($shahkar['ok'] ?? false)) {
            return back()->withInput()->withErrors([
                'identity' => $shahkar['message'] ?? 'شماره تلفن یا مشخصات شما با اطلاعات هویتی شما منطبق نمی‌باشد.',
            ]);
        }

        $kyc = $this->sehatsanji->verifyIdentity(
            idCode: $nationalCode,
            birthDate: $request->string('birth_date')->toString(),
            name: $request->string('first_name')->toString(),
            family: $request->string('family')->toString(),
            fatherName: $request->string('father_name')->toString(),
            nationalId: $nationalCode,
        );

        if (! ($kyc['ok'] ?? false)) {
            $field = $kyc['field'] ?? 'national_code';
            $message = $kyc['message'] ?? 'احراز هویت ناموفق بود.';

            if (in_array($field, ['national_code', 'birth_date', 'first_name', 'family', 'father_name'], true)) {
                return back()->withInput()->withErrors([
                    'identity' => 'شماره تلفن یا مشخصات شما با اطلاعات هویتی شما منطبق نمی‌باشد.',
                    $field => $message,
                ]);
            }

            return back()->withInput()->withErrors([
                $field => $message,
            ]);
        }

        $fullName = trim($request->string('first_name')->toString().' '.$request->string('family')->toString());

        $user->update([
            'name' => $fullName,
            'national_code' => $nationalCode,
        ]);

        session(['otp_sehatsanji_shenase' => $kyc['shenase'] ?? null]);

        return $this->redirectAfterAuth()->with('success', 'ثبت‌نام شما تکمیل شد.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ads.index');
    }

    private function rememberRedirectTarget(mixed $redirect): void
    {
        $target = $this->resolveRedirectTarget(is_string($redirect) ? $redirect : null);

        if ($target) {
            session([
                'post_auth_redirect' => $target,
                'url.intended' => $target,
            ]);
        }
    }

    private function redirectAfterAuth()
    {
        $target = session()->pull('post_auth_redirect')
            ?? session()->pull('url.intended')
            ?? route('ads.index');

        return redirect()->to($target);
    }

    private function resolveRedirectTarget(?string $redirect): ?string
    {
        if (! is_string($redirect) || $redirect === '') {
            return null;
        }

        if (str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            return $redirect;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '' && str_starts_with($redirect, $appUrl)) {
            return $redirect;
        }

        $parsed = parse_url($redirect);
        $path = is_array($parsed) ? ($parsed['path'] ?? null) : null;
        if (! is_string($path) || ! str_starts_with($path, '/')) {
            return null;
        }

        $host = is_array($parsed) ? ($parsed['host'] ?? null) : null;
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        if (is_string($host) && is_string($appHost) && $host !== $appHost) {
            if (! preg_match('#^/(ads|my-ads|admin|auth)(/|$)#', $path) && $path !== '/') {
                return null;
            }
        }

        $query = is_array($parsed) ? ($parsed['query'] ?? null) : null;

        return is_string($query) && $query !== '' ? $path.'?'.$query : $path;
    }

    private function isValidIranianNationalCode(string $nationalCode): bool
    {
        if (! preg_match('/^\d{10}$/', $nationalCode)) {
            return false;
        }

        if (preg_match('/^(\d)\1{9}$/', $nationalCode)) {
            return false;
        }

        $check = (int) substr($nationalCode, 9, 1);
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $nationalCode[$i]) * (10 - $i);
        }

        $remainder = $sum % 11;
        return ($remainder < 2 && $check === $remainder) || ($remainder >= 2 && $check === (11 - $remainder));
    }
}
