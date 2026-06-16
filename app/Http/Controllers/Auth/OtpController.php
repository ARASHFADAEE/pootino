<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\SmsIrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    public function __construct(protected SmsIrService $sms) {}

    public function showPhoneForm() { return view('auth.phone'); }
    public function showVerifyForm() { return view('auth.verify'); }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'name' => ['nullable', 'string', 'max:50'],
        ]);

        $phone = $request->string('phone')->toString();
        $key = 'otp:'.$phone;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return back()->withErrors(['phone' => 'لطفا 2 دقیقه دیگر تلاش کنید.']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Otp::where('phone', $phone)->where('used', false)->delete();
        Otp::create(['phone' => $phone, 'code' => $code, 'expires_at' => now()->addMinutes(2)]);
        $this->sms->sendOtp($phone, $code);
        RateLimiter::hit($key, 120);

        session(['otp_phone' => $phone, 'otp_name' => $request->name]);
        return redirect()->route('auth.otp.verify-form');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $phone = session('otp_phone');
        $otp = Otp::where('phone', $phone)->where('code', $request->code)->where('used', false)->where('expires_at', '>', now())->latest()->first();

        if (! $otp) {
            return back()->withErrors(['code' => 'کد نادرست یا منقضی شده است.']);
        }

        $otp->update(['used' => true]);
        $user = User::firstOrCreate(['phone' => $phone], ['name' => session('otp_name', 'کاربر')]);
        Auth::login($user);
        session()->forget(['otp_phone', 'otp_name']);

        return redirect()->route('ads.index')->with('success', 'خوش آمدید');
    }

    public function resendOtp(Request $request)
    {
        $request->merge(['phone' => session('otp_phone')]);
        return $this->sendOtp($request);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('ads.index');
    }
}
