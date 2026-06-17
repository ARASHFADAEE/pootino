# پرامپت تخصصی Cursor — سایت سرابازان (نسخه کامل)

---

## هدف پروژه

ساخت یک پلتفرم آگهی RTL فارسی برای سربازان وظیفه جهت تبادل محل خدمت.  
**Stack:** Laravel 11 + MySQL  + Livewire/Alpine.js + Tailwind CSS  
**زبان:** فارسی کامل، RTL  
**احراز هویت:** OTP پیامکی از طریق SMS.ir  
**ادمین:** یک ادمین، پنل Filament  
**ربات:** تلگرام برای اطلاع‌رسانی و تایید آگهی  

---

## ۱. نصب و پیکربندی اولیه

```bash
composer create-project laravel/laravel sarbazan
cd sarbazan

# پکیج‌های اصلی
composer require laravel/breeze
composer require filament/filament:"^3.0" -W
composer require morilog/jalali
composer require guzzlehttp/guzzle

# فرانت‌اند
npm install -D tailwindcss @tailwindcss/forms @tailwindcss/typography autoprefixer
npm install alpinejs
```

### فایل `.env`

```env
APP_NAME=پوتینو
APP_URL=http://localhost
APP_LOCALE=fa
APP_TIMEZONE=Asia/Tehran

DB_DATABASE=pootino
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file

# SMS.ir
SMSIR_API_KEY=YOUR_API_KEY_HERE
SMSIR_TEMPLATE_ID=YOUR_TEMPLATE_ID_HERE

# Telegram
TELEGRAM_BOT_TOKEN=YOUR_BOT_TOKEN_HERE
TELEGRAM_ADMIN_CHAT_ID=YOUR_CHAT_ID_HERE
```

### `config/services.php` — اضافه کن:

```php
'smsir' => [
    'api_key'     => env('SMSIR_API_KEY'),
    'template_id' => env('SMSIR_TEMPLATE_ID'),
],

'telegram' => [
    'bot_token'     => env('TELEGRAM_BOT_TOKEN'),
    'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
],
```

---

## ۲. Migrations (به همین ترتیب بساز)

### `create_otps_table`

```php
Schema::create('otps', function (Blueprint $table) {
    $table->id();
    $table->string('phone', 11)->index();
    $table->string('code', 6);
    $table->timestamp('expires_at');
    $table->boolean('used')->default(false);
    $table->timestamps();
});
```

### `create_users_table`

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('phone', 11)->unique();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### `create_provinces_table`

```php
Schema::create('provinces', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
```

### `create_cities_table`

```php
Schema::create('cities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('province_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->timestamps();
});
```

### `create_military_organizations_table`

```php
Schema::create('military_organizations', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // ارتش، سپاه
    $table->timestamps();
});
```

### `create_military_branches_table`

```php
Schema::create('military_branches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')
          ->constrained('military_organizations')
          ->cascadeOnDelete();
    $table->string('name');
    $table->timestamps();
});
```

### `create_ranks_table`

```php
Schema::create('ranks', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->integer('order')->default(0);
    $table->timestamps();
});
```

### `create_education_levels_table`

```php
Schema::create('education_levels', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->integer('order')->default(0);
    $table->timestamps();
});
```

### `create_ads_table` (جدول اصلی)

```php
Schema::create('ads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->string('title', 100);
    $table->text('description')->nullable();

    // محل فعلی
    $table->foreignId('current_province_id')->constrained('provinces');
    $table->foreignId('current_city_id')->constrained('cities');
    $table->foreignId('current_branch_id')->constrained('military_branches');

    // محل مورد نظر
    $table->foreignId('desired_province_id')->constrained('provinces');
    $table->foreignId('desired_city_id')->constrained('cities');

    // اطلاعات سرباز
    $table->foreignId('rank_id')->constrained('ranks');
    $table->foreignId('education_level_id')->constrained('education_levels');

    // شماره تماس — مخفی از غیر لاگین‌شده‌ها
    $table->string('phone', 11);

    // وضعیت
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->text('admin_note')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('expires_at')->nullable(); // 30 روز بعد از تایید
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('views')->default(0);

    // اگر ویرایش شد، دوباره pending میشه
    $table->boolean('edited_after_approval')->default(false);

    $table->timestamps();
    $table->softDeletes();

    $table->index(['status', 'is_active']);
    $table->index(['current_province_id', 'status']);
    $table->index(['desired_province_id', 'status']);
});
```

---

## ۳. Seeders

### `DatabaseSeeder.php`

```php
public function run(): void
{
    $this->call([
        RankSeeder::class,
        EducationLevelSeeder::class,
        MilitaryOrganizationSeeder::class,
        ProvinceAndCitySeeder::class,
        AdminSeeder::class,
    ]);
}
```

### `RankSeeder.php`

```php
$ranks = [
    ['name' => 'سرباز دوم',    'order' => 1],
    ['name' => 'سرباز اول',    'order' => 2],
    ['name' => 'سرجوخه',       'order' => 3],
    ['name' => 'گروهبان سوم', 'order' => 4],
    ['name' => 'گروهبان دوم', 'order' => 5],
    ['name' => 'گروهبان یکم', 'order' => 6],
    ['name' => 'استوار دوم',  'order' => 7],
    ['name' => 'استوار یکم',  'order' => 8],
    ['name' => 'استوار ارشد', 'order' => 9],
];
foreach ($ranks as $rank) Rank::create($rank);
```

### `EducationLevelSeeder.php`

```php
$levels = [
    ['name' => 'دیپلم',           'order' => 1],
    ['name' => 'فوق دیپلم',       'order' => 2],
    ['name' => 'کارشناسی',        'order' => 3],
    ['name' => 'کارشناسی ارشد',   'order' => 4],
    ['name' => 'دکترا',           'order' => 5],
];
foreach ($levels as $level) EducationLevel::create($level);
```

### `MilitaryOrganizationSeeder.php`

```php
$data = [
    'ارتش جمهوری اسلامی ایران' => [
        'نیروی زمینی ارتش',
        'نیروی هوایی ارتش',
        'نیروی دریایی ارتش',
        'نیروی پدافند هوایی ارتش',
    ],
    'سپاه پاسداران انقلاب اسلامی' => [
        'نیروی زمینی سپاه',
        'نیروی هوایی سپاه',
        'نیروی دریایی سپاه',
        'نیروی قدس',
        'سازمان بسیج مستضعفین',
        'سازمان فضایی سپاه',
    ],
];

foreach ($data as $orgName => $branches) {
    $org = MilitaryOrganization::create(['name' => $orgName]);
    foreach ($branches as $branch) {
        MilitaryBranch::create(['organization_id' => $org->id, 'name' => $branch]);
    }
}
```

### `AdminSeeder.php` — یوزر ادمین برای Filament

```php
use App\Models\Admin;
Admin::create([
    'name'     => 'مدیر سیستم',
    'email'    => 'admin@sarbazan.ir',
    'password' => bcrypt('AdminPass@123'), // بعد از deploy عوض کن
]);
```

### `ProvinceAndCitySeeder.php`
تمام ۳۱ استان ایران و شهرهای اصلی هر استان را seed کن. حداقل ۵ شهر بزرگ برای هر استان.

---

## ۴. Models

### `app/Models/Otp.php`

```php
class Otp extends Model
{
    protected $fillable = ['phone', 'code', 'expires_at', 'used'];
    protected $casts    = ['expires_at' => 'datetime'];

    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
```

### `app/Models/User.php`

```php
class User extends Authenticatable
{
    protected $fillable = ['name', 'phone', 'is_active'];

    // بدون password — ورود فقط با OTP
    public function getAuthPassword() { return null; }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }
}
```

### `app/Models/Ad.php`

```php
class Ad extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'description',
        'current_province_id', 'current_city_id', 'current_branch_id',
        'desired_province_id', 'desired_city_id',
        'rank_id', 'education_level_id',
        'phone', 'status', 'admin_note',
        'approved_at', 'expires_at', 'is_active', 'views',
        'edited_after_approval',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'expires_at'  => 'datetime',
        'is_active'   => 'boolean',
        'edited_after_approval' => 'boolean',
    ];

    // ====== Scopes ======

    public function scopeApproved($query)
    {
        return $query
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['current_province_id'] ?? null,
                fn($q, $v) => $q->where('current_province_id', $v))
            ->when($filters['current_city_id'] ?? null,
                fn($q, $v) => $q->where('current_city_id', $v))
            ->when($filters['desired_province_id'] ?? null,
                fn($q, $v) => $q->where('desired_province_id', $v))
            ->when($filters['desired_city_id'] ?? null,
                fn($q, $v) => $q->where('desired_city_id', $v))
            ->when($filters['rank_id'] ?? null,
                fn($q, $v) => $q->where('rank_id', $v))
            ->when($filters['education_level_id'] ?? null,
                fn($q, $v) => $q->where('education_level_id', $v))
            ->when($filters['branch_id'] ?? null,
                fn($q, $v) => $q->where('current_branch_id', $v))
            ->when($filters['organization_id'] ?? null,
                fn($q, $v) => $q->whereHas('currentBranch',
                    fn($bq) => $bq->where('organization_id', $v)));
    }

    // ====== Relationships ======

    public function user()            { return $this->belongsTo(User::class); }
    public function currentProvince() { return $this->belongsTo(Province::class, 'current_province_id'); }
    public function currentCity()     { return $this->belongsTo(City::class, 'current_city_id'); }
    public function currentBranch()   { return $this->belongsTo(MilitaryBranch::class, 'current_branch_id'); }
    public function desiredProvince() { return $this->belongsTo(Province::class, 'desired_province_id'); }
    public function desiredCity()     { return $this->belongsTo(City::class, 'desired_city_id'); }
    public function rank()            { return $this->belongsTo(Rank::class); }
    public function educationLevel()  { return $this->belongsTo(EducationLevel::class); }

    // ====== Helpers ======

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'در انتظار تایید',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            default    => '-',
        };
    }
}
```

### `app/Models/Admin.php` — جدا از User، برای Filament

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class Admin extends Authenticatable implements FilamentUser
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password'];
    protected $casts    = ['password' => 'hashed'];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
```

---

## ۵. سرویس SMS.ir

### `app/Services/SmsIrService.php`

```php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsIrService
{
    protected string $apiKey;
    protected int    $templateId;
    protected string $baseUrl = 'https://api.sms.ir/v1';

    public function __construct()
    {
        $this->apiKey     = config('services.smsir.api_key');
        $this->templateId = (int) config('services.smsir.template_id');
    }

    /**
     * ارسال کد OTP به شماره موبایل
     */
    public function sendOtp(string $mobile, string $code): bool
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'text/plain',
                'x-api-key'    => $this->apiKey,
            ])->post("{$this->baseUrl}/send/verify", [
                'mobile'     => $mobile,
                'templateId' => $this->templateId,
                'parameters' => [
                    ['name' => 'CODE', 'value' => $code],
                ],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('SMS.ir error', [
                'status'   => $response->status(),
                'response' => $response->body(),
                'mobile'   => $mobile,
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('SMS.ir exception: ' . $e->getMessage());
            return false;
        }
    }
}
```

### اضافه کردن به `AppServiceProvider`

```php
use App\Services\SmsIrService;

$this->app->singleton(SmsIrService::class, fn() => new SmsIrService());
```

---

## ۶. احراز هویت OTP

### `app/Http/Controllers/Auth/OtpController.php`

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\SmsIrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function __construct(protected SmsIrService $sms) {}

    // مرحله ۱: فرم ورود شماره موبایل
    public function showPhoneForm()
    {
        return view('auth.phone');
    }

    // مرحله ۱: ارسال OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'name'  => ['required_if:is_register,1', 'string', 'max:50'],
        ], [
            'phone.regex' => 'شماره موبایل معتبر وارد کنید (مثال: 09123456789)',
            'name.required_if' => 'نام الزامی است.',
        ]);

        $phone = $request->phone;

        // Rate limiting: هر شماره هر ۲ دقیقه یک OTP
        $key = 'otp:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'phone' => "لطفاً {$seconds} ثانیه دیگر تلاش کنید.",
            ]);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::where('phone', $phone)->where('used', false)->delete();

        Otp::create([
            'phone'      => $phone,
            'code'       => $code,
            'expires_at' => now()->addMinutes(2),
        ]);

        $sent = $this->sms->sendOtp($phone, $code);

        if (!$sent) {
            return back()->withErrors(['phone' => 'خطا در ارسال پیامک. لطفاً دوباره تلاش کنید.']);
        }

        RateLimiter::hit($key, 120); // 2 دقیقه

        // ذخیره اطلاعات موقت در session
        session(['otp_phone' => $phone, 'otp_name' => $request->name]);

        return redirect()->route('auth.otp.verify-form');
    }

    // مرحله ۲: فرم تایید کد
    public function showVerifyForm()
    {
        if (!session('otp_phone')) {
            return redirect()->route('auth.otp.phone');
        }
        return view('auth.verify');
    }

    // مرحله ۲: تایید کد و ورود/ثبت‌نام
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'کد تایید الزامی است.',
            'code.digits'   => 'کد تایید باید ۶ رقم باشد.',
        ]);

        $phone = session('otp_phone');

        if (!$phone) {
            return redirect()->route('auth.otp.phone')
                ->withErrors(['code' => 'جلسه منقضی شده. دوباره شماره خود را وارد کنید.']);
        }

        $otp = Otp::where('phone', $phone)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'کد وارد شده نادرست یا منقضی شده است.']);
        }

        $otp->update(['used' => true]);

        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => session('otp_name', 'کاربر')]
        );

        if (!$user->is_active) {
            return redirect()->route('auth.otp.phone')
                ->withErrors(['phone' => 'حساب کاربری شما غیرفعال شده است.']);
        }

        Auth::login($user);
        session()->forget(['otp_phone', 'otp_name']);

        return redirect()->intended(route('ads.index'))
            ->with('success', 'خوش آمدید، ' . $user->name);
    }

    // ارسال مجدد کد
    public function resendOtp(Request $request)
    {
        $phone = session('otp_phone');

        if (!$phone) {
            return redirect()->route('auth.otp.phone');
        }

        // حذف OTP قبلی و ارسال مجدد
        $request->merge(['phone' => $phone]);
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
```

---

## ۷. Controllers اصلی

### `app/Http/Controllers/AdController.php`

```php
namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Jobs\SendAdToTelegramJob;
use App\Models\Ad;
use App\Models\City;
use App\Models\EducationLevel;
use App\Models\MilitaryOrganization;
use App\Models\Province;
use App\Models\Rank;
use Illuminate\Http\Request;

class AdController extends Controller
{
    // لیست آگهی‌ها با فیلتر
    public function index(Request $request)
    {
        $filters = $request->only([
            'current_province_id', 'current_city_id',
            'desired_province_id', 'desired_city_id',
            'rank_id', 'education_level_id',
            'branch_id', 'organization_id', 'search',
        ]);

        $ads = Ad::approved()
            ->filter($filters)
            ->with([
                'currentProvince', 'currentCity',
                'currentBranch.organization',
                'desiredProvince', 'desiredCity',
                'rank', 'educationLevel',
            ])
            ->when(
                $request->search,
                fn($q, $s) => $q->where('title', 'like', "%{$s}%")
                               ->orWhere('description', 'like', "%{$s}%")
            )
            ->latest('approved_at')
            ->paginate(12)
            ->withQueryString();

        $provinces     = Province::orderBy('name')->get();
        $organizations = MilitaryOrganization::with('branches')->get();
        $ranks         = Rank::orderBy('order')->get();
        $educationLevels = EducationLevel::orderBy('order')->get();

        // تعداد کل آگهی‌های فعال برای نمایش در hero
        $totalActive = Ad::approved()->count();

        return view('ads.index', compact(
            'ads', 'provinces', 'organizations',
            'ranks', 'educationLevels', 'filters', 'totalActive'
        ));
    }

    // جزئیات آگهی
    public function show(Ad $ad)
    {
        abort_if($ad->status !== 'approved' || !$ad->is_active, 404);

        $ad->increment('views');

        $ad->load([
            'user',
            'currentProvince', 'currentCity', 'currentBranch.organization',
            'desiredProvince', 'desiredCity',
            'rank', 'educationLevel',
        ]);

        // آگهی‌های مشابه (همان استان مقصد)
        $similarAds = Ad::approved()
            ->where('id', '!=', $ad->id)
            ->where('desired_province_id', $ad->desired_province_id)
            ->with(['currentCity', 'desiredCity', 'rank'])
            ->limit(4)
            ->get();

        return view('ads.show', compact('ad', 'similarAds'));
    }

    // فرم ثبت آگهی
    public function create()
    {
        $provinces     = Province::orderBy('name')->get();
        $organizations = MilitaryOrganization::with('branches')->get();
        $ranks         = Rank::orderBy('order')->get();
        $educationLevels = EducationLevel::orderBy('order')->get();

        return view('ads.create', compact('provinces', 'organizations', 'ranks', 'educationLevels'));
    }

    // ذخیره آگهی
    public function store(StoreAdRequest $request)
    {
        // محدودیت: هر کاربر حداکثر ۲ آگهی فعال (pending یا approved)
        $activeCount = auth()->user()->ads()
            ->whereIn('status', ['pending', 'approved'])
            ->where('is_active', true)
            ->count();

        if ($activeCount >= 2) {
            return back()->withErrors([
                'limit' => 'شما حداکثر ۲ آگهی فعال می‌توانید داشته باشید.',
            ]);
        }

        $ad = auth()->user()->ads()->create([
            ...$request->validated(),
            'expires_at' => null, // بعد از تایید ادمین ست میشه
        ]);

        dispatch(new SendAdToTelegramJob($ad));

        return redirect()->route('ads.my')
            ->with('success', 'آگهی شما ثبت شد و پس از تایید مدیر نمایش داده خواهد شد.');
    }

    // فرم ویرایش آگهی
    public function edit(Ad $ad)
    {
        abort_if($ad->user_id !== auth()->id(), 403);
        abort_if(!in_array($ad->status, ['approved', 'pending', 'rejected']), 403);

        $provinces     = Province::orderBy('name')->get();
        $organizations = MilitaryOrganization::with('branches')->get();
        $ranks         = Rank::orderBy('order')->get();
        $educationLevels = EducationLevel::orderBy('order')->get();

        return view('ads.edit', compact('ad', 'provinces', 'organizations', 'ranks', 'educationLevels'));
    }

    // ذخیره ویرایش — آگهی بعد از ویرایش pending میشه
    public function update(UpdateAdRequest $request, Ad $ad)
    {
        abort_if($ad->user_id !== auth()->id(), 403);

        $ad->update([
            ...$request->validated(),
            'status'                 => 'pending',
            'approved_at'            => null,
            'expires_at'             => null,
            'edited_after_approval'  => true,
        ]);

        dispatch(new SendAdToTelegramJob($ad, isEdit: true));

        return redirect()->route('ads.my')
            ->with('success', 'آگهی ویرایش شد و منتظر تایید مدیر است.');
    }

    // حذف آگهی توسط کاربر
    public function destroy(Ad $ad)
    {
        abort_if($ad->user_id !== auth()->id(), 403);
        $ad->delete();
        return redirect()->route('ads.my')->with('success', 'آگهی حذف شد.');
    }

    // آگهی‌های من
    public function myAds()
    {
        $ads = auth()->user()->ads()
            ->with(['currentCity', 'desiredCity', 'rank'])
            ->latest()
            ->paginate(10);

        return view('ads.my-ads', compact('ads'));
    }
}
```

### `app/Http/Requests/StoreAdRequest.php`

```php
class StoreAdRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'title'               => 'required|string|max:100',
            'description'         => 'nullable|string|max:1000',
            'current_province_id' => 'required|exists:provinces,id',
            'current_city_id'     => 'required|exists:cities,id',
            'current_branch_id'   => 'required|exists:military_branches,id',
            'desired_province_id' => 'required|exists:provinces,id',
            'desired_city_id'     => 'required|exists:cities,id',
            'rank_id'             => 'required|exists:ranks,id',
            'education_level_id'  => 'required|exists:education_levels,id',
            'phone'               => ['required', 'regex:/^09[0-9]{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex'               => 'شماره موبایل باید با ۰۹ شروع و ۱۱ رقم باشد.',
            'title.required'            => 'عنوان آگهی الزامی است.',
            'current_province_id.required' => 'استان محل خدمت الزامی است.',
            'current_city_id.required'  => 'شهر محل خدمت الزامی است.',
            'current_branch_id.required'=> 'نیروی مسلح الزامی است.',
            'desired_province_id.required' => 'استان مقصد الزامی است.',
            'desired_city_id.required'  => 'شهر مقصد الزامی است.',
            'rank_id.required'          => 'درجه نظامی الزامی است.',
            'education_level_id.required' => 'مدرک تحصیلی الزامی است.',
        ];
    }
}
```

`UpdateAdRequest` همانند `StoreAdRequest` باشد.

---

## ۸. Jobs

### `app/Jobs/SendAdToTelegramJob.php`

```php
namespace App\Jobs;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendAdToTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Ad $ad,
        public bool $isEdit = false
    ) {}

    public function handle(): void
    {
        $ad = $this->ad->load([
            'user',
            'currentProvince', 'currentCity', 'currentBranch.organization',
            'desiredProvince', 'desiredCity',
            'rank', 'educationLevel',
        ]);

        $editLabel = $this->isEdit ? '✏️ *ویرایش آگهی*' : '🆕 *آگهی جدید*';

        $text  = "{$editLabel}\n\n";
        $text .= "📋 *{$ad->title}*\n\n";
        $text .= "📍 محل فعلی: {$ad->currentCity->name} — {$ad->currentProvince->name}\n";
        $text .= "🎯 محل مورد نظر: {$ad->desiredCity->name} — {$ad->desiredProvince->name}\n";
        $text .= "🏛️ نیرو: {$ad->currentBranch->organization->name} › {$ad->currentBranch->name}\n";
        $text .= "⭐ درجه: {$ad->rank->name}\n";
        $text .= "🎓 تحصیلات: {$ad->educationLevel->name}\n";
        $text .= "📞 شماره تماس: {$ad->phone}\n\n";
        $text .= "👤 کاربر: {$ad->user->name} | {$ad->user->phone}\n";
        $text .= "🆔 شناسه آگهی: \\#{$ad->id}\n\n";
        $text .= "✅ تایید: /approve\\_{$ad->id}\n";
        $text .= "❌ رد: /reject\\_{$ad->id}";

        $botToken = config('services.telegram.bot_token');
        $chatId   = config('services.telegram.admin_chat_id');

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'MarkdownV2',
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram send failed: ' . $e->getMessage());
        }
    }
}
```

### `app/Http/Controllers/TelegramWebhookController.php`

```php
namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();
        $text   = trim($update['message']['text'] ?? '');
        $chatId = $update['message']['chat']['id'] ?? null;
        $token  = config('services.telegram.bot_token');

        if (!$chatId) return response()->json(['ok' => true]);

        // تایید آگهی
        if (preg_match('/^\/approve_(\d+)$/', $text, $m)) {
            $ad = Ad::find((int) $m[1]);
            if ($ad) {
                $ad->update([
                    'status'      => 'approved',
                    'approved_at' => now(),
                    'expires_at'  => now()->addDays(30),
                ]);
                $msg = "✅ آگهی #{$ad->id} تایید و منتشر شد.";
            } else {
                $msg = "⚠️ آگهی یافت نشد.";
            }
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId, 'text' => $msg,
            ]);
        }

        // رد آگهی
        if (preg_match('/^\/reject_(\d+)$/', $text, $m)) {
            $ad = Ad::find((int) $m[1]);
            if ($ad) {
                $ad->update(['status' => 'rejected']);
                $msg = "❌ آگهی #{$ad->id} رد شد.";
            } else {
                $msg = "⚠️ آگهی یافت نشد.";
            }
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId, 'text' => $msg,
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
```

---

## ۹. Filament Admin Panel

### `app/Filament/Resources/AdResource.php`

```php
namespace App\Filament\Resources;

use App\Filament\Resources\AdResource\Pages;
use App\Models\Ad;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;
    protected static ?string $navigationLabel = 'آگهی‌ها';
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $modelLabel      = 'آگهی';
    protected static ?string $pluralModelLabel = 'آگهی‌ها';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('شناسه')->sortable()->searchable(),
                TextColumn::make('title')->label('عنوان')->searchable()->limit(40),
                TextColumn::make('user.name')->label('کاربر')->searchable(),
                TextColumn::make('user.phone')->label('موبایل'),
                TextColumn::make('currentCity.name')->label('محل فعلی'),
                TextColumn::make('desiredCity.name')->label('محل مقصد'),
                TextColumn::make('rank.name')->label('درجه'),
                TextColumn::make('phone')->label('شماره تماس'),
                TextColumn::make('views')->label('بازدید')->sortable(),
                BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending'  => 'در انتظار',
                        'approved' => 'تایید شده',
                        'rejected' => 'رد شده',
                    }),
                TextColumn::make('created_at')->label('تاریخ ثبت')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending'  => 'در انتظار',
                        'approved' => 'تایید شده',
                        'rejected' => 'رد شده',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('تایید')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn(Ad $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn(Ad $record) => $record->update([
                        'status'      => 'approved',
                        'approved_at' => now(),
                        'expires_at'  => now()->addDays(30),
                    ])),

                Action::make('reject')
                    ->label('رد')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn(Ad $record) => $record->status !== 'rejected')
                    ->form([
                        Textarea::make('admin_note')
                            ->label('دلیل رد (اختیاری)')
                            ->maxLength(300),
                    ])
                    ->action(fn(Ad $record, array $data) => $record->update([
                        'status'     => 'rejected',
                        'admin_note' => $data['admin_note'] ?? null,
                    ])),

                Tables\Actions\ViewAction::make()->label('مشاهده'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAds::route('/'),
            'view'  => Pages\ViewAd::route('/{record}'),
        ];
    }
}
```

---

## ۱۰. Routes

```php
// routes/web.php

use App\Http\Controllers\AdController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\TelegramWebhookController;

// ===== صفحات اصلی =====
Route::get('/', [AdController::class, 'index'])->name('ads.index');
Route::get('/ads/{ad}', [AdController::class, 'show'])->name('ads.show');

// ===== آگهی‌های کاربر (نیاز به ورود) =====
Route::middleware('auth')->group(function () {
    Route::get('/ads/create', [AdController::class, 'create'])->name('ads.create');
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::get('/ads/{ad}/edit', [AdController::class, 'edit'])->name('ads.edit');
    Route::put('/ads/{ad}', [AdController::class, 'update'])->name('ads.update');
    Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');
    Route::get('/my-ads', [AdController::class, 'myAds'])->name('ads.my');
});

// ===== احراز هویت OTP =====
Route::prefix('auth')->name('auth.otp.')->group(function () {
    Route::get('/phone',         [OtpController::class, 'showPhoneForm'])->name('phone');
    Route::post('/send-otp',     [OtpController::class, 'sendOtp'])->name('send');
    Route::get('/verify',        [OtpController::class, 'showVerifyForm'])->name('verify-form');
    Route::post('/verify',       [OtpController::class, 'verifyOtp'])->name('verify');
    Route::post('/resend',       [OtpController::class, 'resendOtp'])->name('resend');
    Route::post('/logout',       [OtpController::class, 'logout'])->name('logout');
});

// ===== AJAX endpoints =====
Route::get('/api/cities/{province}', function (\App\Models\Province $province) {
    return $province->cities()->orderBy('name')->get(['id', 'name']);
})->name('api.cities');

Route::get('/api/branches/{organization}', function (\App\Models\MilitaryOrganization $organization) {
    return $organization->branches()->orderBy('name')->get(['id', 'name']);
})->name('api.branches');

// ===== Telegram Webhook =====
Route::post('/telegram/webhook/{secret}', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');
// secret یک رشته تصادفی است که در env ذخیره می‌کنی تا endpoint ایمن باشد
```

---

## ۱۱. Views (Blade) — ساختار کامل

### فایل‌های View که باید بسازی:

```
resources/views/
├── layouts/
│   ├── app.blade.php          ← layout اصلی با RTL + Vazirmatn
│   └── auth.blade.php         ← layout صفحات ورود
├── components/
│   ├── ad-card.blade.php      ← کامپوننت کارت آگهی
│   ├── filter-sidebar.blade.php
│   ├── phone-badge.blade.php  ← نمایش/مخفی شماره
│   └── empty-state.blade.php
├── ads/
│   ├── index.blade.php        ← لیست + فیلتر
│   ├── show.blade.php         ← جزئیات آگهی
│   ├── create.blade.php       ← فرم ثبت آگهی
│   ├── edit.blade.php         ← فرم ویرایش
│   └── my-ads.blade.php       ← آگهی‌های من
└── auth/
    ├── phone.blade.php        ← فرم ورود شماره
    └── verify.blade.php       ← فرم کد OTP
```

### `layouts/app.blade.php` — نکات مهم

```html
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'سرابازان — تبادل محل خدمت')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-cream font-sans text-gray-800 antialiased">

    <!-- Navbar -->
    <nav class="bg-military sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <!-- لوگو -->
            <a href="{{ route('ads.index') }}" class="text-white font-bold text-xl">
                🪖 سرابازان
            </a>

            <!-- منو دسکتاپ -->
            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('ads.index') }}" class="text-cream hover:text-white text-sm">
                    آگهی‌ها
                </a>

                @auth
                    <a href="{{ route('ads.create') }}"
                       class="bg-amber-500 hover:bg-amber-400 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                        + ثبت آگهی
                    </a>
                    <a href="{{ route('ads.my') }}" class="text-cream hover:text-white text-sm">
                        آگهی‌های من
                    </a>
                    <form action="{{ route('auth.otp.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-cream hover:text-white text-sm">
                            خروج
                        </button>
                    </form>
                @else
                    <a href="{{ route('auth.otp.phone') }}"
                       class="bg-white text-military text-sm px-4 py-2 rounded-lg font-medium hover:bg-cream transition">
                        ورود / ثبت‌نام
                    </a>
                @endauth
            </div>

            <!-- همبرگر موبایل -->
            <button class="md:hidden text-white" x-data @click="$dispatch('toggle-menu')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden" x-data="{ open: false }" @toggle-menu.window="open = !open"
             x-show="open" x-collapse>
            <!-- آیتم‌های منو موبایل -->
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- محتوا -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-military-dark text-cream/70 text-center text-sm py-6 mt-16">
        <p>سرابازان — پلتفرم تبادل محل خدمت سربازان وظیفه | {{ now()->format('Y') }}</p>
    </footer>

</body>
</html>
```

### ساختار `ads/index.blade.php`

```
بخش Hero (کوتاه، بالای صفحه):
  - عنوان: "تبادل محل خدمت، ساده‌تر از همیشه"
  - زیرعنوان: "X آگهی فعال از سراسر کشور"
  - input جستجو

بدنه اصلی (grid 2-ستونه):
  چپ (sidebar w-72 در دسکتاپ):
    - فیلتر "استان محل فعلی" (select)
    - فیلتر "شهر محل فعلی" (select — وابسته)
    - فیلتر "استان مقصد" (select)
    - فیلتر "شهر مقصد" (select — وابسته)
    - فیلتر "سازمان" (ارتش/سپاه)
    - فیلتر "نیرو/شاخه" (وابسته)
    - فیلتر "درجه"
    - فیلتر "تحصیلات"
    - دکمه "اعمال فیلتر" + "پاک کردن"

  راست (grid 3-ستونه در دسکتاپ، 2-ستونه تبلت، 1-ستونه موبایل):
    - کارت‌های آگهی (component: ad-card)
    - Empty state اگر نتیجه‌ای نباشد
    - Pagination فارسی

موبایل: دکمه "فیلترها" که Drawer از پایین باز میکنه (Alpine.js)
```

### ساختار کارت آگهی `components/ad-card.blade.php`

```html
<div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md
            transition-shadow p-4 flex flex-col gap-3">

    <!-- Header: وضعیت + تاریخ -->
    <div class="flex justify-between items-start">
        <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full font-medium">
            فعال
        </span>
        <span class="text-xs text-gray-400">
            {{ \Morilog\Jalali\Jalalian::fromCarbon($ad->approved_at)->format('j M Y') }}
        </span>
    </div>

    <!-- عنوان -->
    <h3 class="font-semibold text-gray-900 text-sm leading-6 line-clamp-2">
        {{ $ad->title }}
    </h3>

    <!-- جزئیات -->
    <div class="space-y-2 text-xs text-gray-600">
        <div class="flex items-center gap-2">
            <span class="text-military">📍</span>
            <span>محل فعلی: <strong>{{ $ad->currentCity->name }}</strong>
                  — {{ $ad->currentProvince->name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-amber-500">🎯</span>
            <span>مقصد: <strong>{{ $ad->desiredCity->name }}</strong>
                  — {{ $ad->desiredProvince->name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span>🏛️</span>
            <span>{{ $ad->currentBranch->organization->name }}</span>
        </div>
        <div class="flex gap-4">
            <span>⭐ {{ $ad->rank->name }}</span>
            <span>🎓 {{ $ad->educationLevel->name }}</span>
        </div>
    </div>

    <!-- Footer -->
    <div class="flex justify-between items-center mt-auto pt-2 border-t border-gray-50">
        <span class="text-xs text-gray-400">{{ $ad->views }} بازدید</span>
        <a href="{{ route('ads.show', $ad) }}"
           class="text-xs bg-military text-white px-3 py-1.5 rounded-lg hover:bg-military-light transition">
            مشاهده آگهی
        </a>
    </div>
</div>
```

### نمایش شماره تماس در `ads/show.blade.php`

```blade
@auth
    {{-- کاربر لاگین کرده: نمایش واقعی --}}
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
        <span class="text-2xl">📞</span>
        <div>
            <p class="text-xs text-gray-500 mb-1">شماره تماس سرباز</p>
            <p class="text-xl font-bold text-green-700 tracking-wider" dir="ltr">
                {{ $ad->phone }}
            </p>
        </div>
    </div>
@else
    {{-- غیرلاگین: blur با دعوت به ورود --}}
    <div class="relative bg-gray-50 border border-gray-200 rounded-xl p-4">
        <p class="text-xl font-bold text-gray-800 blur-sm select-none" dir="ltr">
            09XX XXXX XX
        </p>
        <div class="absolute inset-0 flex flex-col items-center justify-center
                    bg-white/80 backdrop-blur-sm rounded-xl gap-2">
            <p class="text-sm text-gray-600">برای مشاهده شماره تماس وارد شوید</p>
            <a href="{{ route('auth.otp.phone') }}"
               class="bg-military text-white text-sm px-5 py-2 rounded-lg hover:bg-military-light transition">
                ورود / ثبت‌نام
            </a>
        </div>
    </div>
@endauth
```

---

## ۱۲. فرم ثبت آگهی — Alpine.js برای select وابسته

### `ads/create.blade.php`

```html
@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto py-10 px-4"
     x-data="{
         currentProvinceId: '',
         currentCities: [],
         desiredProvinceId: '',
         desiredCities: [],
         organizationId: '',
         branches: [],

         async fetchCities(provinceId, target) {
             if (!provinceId) { this[target] = []; return; }
             const res = await fetch('/api/cities/' + provinceId);
             this[target] = await res.json();
         },

         async fetchBranches(orgId) {
             if (!orgId) { this.branches = []; return; }
             const res = await fetch('/api/branches/' + orgId);
             this.branches = await res.json();
         }
     }">

    <h1 class="text-2xl font-bold text-gray-900 mb-8">ثبت آگهی تبادل محل خدمت</h1>

    <form action="{{ route('ads.store') }}" method="POST" class="space-y-8">
        @csrf

        <!-- بخش ۱: محل خدمت فعلی -->
        <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
            <h2 class="text-base font-semibold text-military border-r-4 border-military pr-3">
                محل خدمت فعلی
            </h2>

            <!-- استان -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    استان <span class="text-red-500">*</span>
                </label>
                <select name="current_province_id"
                        x-model="currentProvinceId"
                        @change="fetchCities(currentProvinceId, 'currentCities')"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                               focus:ring-2 focus:ring-military/20 focus:border-military">
                    <option value="">انتخاب استان</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p->id }}" @selected(old('current_province_id') == $p->id)>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
                @error('current_province_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- شهر (وابسته به استان) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    شهر <span class="text-red-500">*</span>
                </label>
                <select name="current_city_id"
                        :disabled="!currentCities.length"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                               focus:ring-2 focus:ring-military/20 focus:border-military
                               disabled:bg-gray-50 disabled:text-gray-400">
                    <option value="">ابتدا استان را انتخاب کنید</option>
                    <template x-for="city in currentCities" :key="city.id">
                        <option :value="city.id" x-text="city.name"></option>
                    </template>
                </select>
            </div>

            <!-- سازمان (ارتش/سپاه) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    سازمان <span class="text-red-500">*</span>
                </label>
                <select name="organization_id"
                        x-model="organizationId"
                        @change="fetchBranches(organizationId)"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                    <option value="">انتخاب سازمان</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- نیرو/شاخه (وابسته به سازمان) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    نیروی مسلح <span class="text-red-500">*</span>
                </label>
                <select name="current_branch_id"
                        :disabled="!branches.length"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                               disabled:bg-gray-50 disabled:text-gray-400">
                    <option value="">ابتدا سازمان را انتخاب کنید</option>
                    <template x-for="branch in branches" :key="branch.id">
                        <option :value="branch.id" x-text="branch.name"></option>
                    </template>
                </select>
            </div>
        </div>

        <!-- بخش ۲: محل مورد نظر -->
        <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
            <h2 class="text-base font-semibold text-amber-600 border-r-4 border-amber-500 pr-3">
                محل مورد نظر برای تبادل
            </h2>
            <!-- همین ساختار برای desired_province_id و desired_city_id -->
        </div>

        <!-- بخش ۳: اطلاعات شخصی -->
        <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 border-r-4 border-gray-300 pr-3">
                اطلاعات شخصی
            </h2>

            <!-- درجه -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    درجه نظامی <span class="text-red-500">*</span>
                </label>
                <select name="rank_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                    <option value="">انتخاب درجه</option>
                    @foreach($ranks as $rank)
                        <option value="{{ $rank->id }}" @selected(old('rank_id') == $rank->id)>
                            {{ $rank->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- مدرک تحصیلی -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    مدرک تحصیلی <span class="text-red-500">*</span>
                </label>
                <select name="education_level_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                    <option value="">انتخاب مدرک</option>
                    @foreach($educationLevels as $level)
                        <option value="{{ $level->id }}" @selected(old('education_level_id') == $level->id)>
                            {{ $level->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- شماره تماس -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    شماره تماس <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="phone" dir="ltr"
                       value="{{ old('phone', auth()->user()->phone) }}"
                       placeholder="09123456789"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
                <p class="text-xs text-gray-400 mt-1">
                    این شماره فقط برای کاربران ثبت‌نام‌شده قابل مشاهده است.
                </p>
            </div>
        </div>

        <!-- بخش ۴: عنوان و توضیحات -->
        <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 border-r-4 border-gray-300 pr-3">
                متن آگهی
            </h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    عنوان آگهی <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}"
                       placeholder="مثال: دنبال تبادل تهران به اصفهان هستم"
                       maxlength="100"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    توضیحات بیشتر (اختیاری)
                </label>
                <textarea name="description" rows="3" maxlength="1000"
                          placeholder="توضیحات تکمیلی درباره شرایط تبادل..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm resize-none">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- دکمه ثبت -->
        <button type="submit"
                class="w-full bg-military text-white font-semibold py-3 rounded-xl
                       hover:bg-military-light transition text-sm">
            ثبت آگهی
        </button>
    </form>
</div>
@endsection
```

---

## ۱۳. صفحات احراز هویت OTP

### `auth/phone.blade.php`

```html
@extends('layouts.auth')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-cream px-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

        <div class="text-center mb-8">
            <div class="text-4xl mb-3">🪖</div>
            <h1 class="text-xl font-bold text-gray-900">ورود به سرابازان</h1>
            <p class="text-sm text-gray-500 mt-1">شماره موبایل خود را وارد کنید</p>
        </div>

        <form action="{{ route('auth.otp.send') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نام (برای کاربران جدید)</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="نام و نام خانوادگی"
                       class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm
                              focus:ring-2 focus:ring-military/20 focus:border-military">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    شماره موبایل <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       dir="ltr" placeholder="09123456789"
                       class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm
                              focus:ring-2 focus:ring-military/20 focus:border-military text-center">
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-military text-white font-semibold py-3 rounded-xl
                           hover:bg-military-light transition">
                دریافت کد تایید
            </button>

            <p class="text-center text-xs text-gray-400">
                ورود به معنای قبول <a href="#" class="text-military underline">قوانین سایت</a> است.
            </p>
        </form>
    </div>
</div>
@endsection
```

### `auth/verify.blade.php`

```html
@extends('layouts.auth')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-cream px-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border border-gray-100 p-8"
         x-data="{ timer: 120, canResend: false, interval: null,
                   init() {
                     this.interval = setInterval(() => {
                       if (this.timer > 0) { this.timer--; }
                       else { this.canResend = true; clearInterval(this.interval); }
                     }, 1000);
                   } }">

        <div class="text-center mb-8">
            <div class="text-4xl mb-3">📱</div>
            <h1 class="text-xl font-bold text-gray-900">کد تایید</h1>
            <p class="text-sm text-gray-500 mt-1">
                کد ۶ رقمی ارسال‌شده به
                <span class="font-medium text-military" dir="ltr">{{ session('otp_phone') }}</span>
                را وارد کنید
            </p>
        </div>

        <form action="{{ route('auth.otp.verify') }}" method="POST" class="space-y-4">
            @csrf

            <!-- ورودی ۶ رقمی کد -->
            <div>
                <input type="text" name="code" inputmode="numeric"
                       maxlength="6" dir="ltr"
                       placeholder="------"
                       autofocus
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-4 text-2xl
                              font-bold text-center tracking-[0.5em]
                              focus:ring-2 focus:ring-military/20 focus:border-military">
                @error('code')
                    <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-military text-white font-semibold py-3 rounded-xl
                           hover:bg-military-light transition">
                تایید و ورود
            </button>
        </form>

        <!-- تایمر و ارسال مجدد -->
        <div class="mt-4 text-center">
            <template x-if="!canResend">
                <p class="text-sm text-gray-400">
                    ارسال مجدد تا
                    <span class="text-military font-medium" x-text="timer + ' ثانیه'"></span>
                </p>
            </template>
            <template x-if="canResend">
                <form action="{{ route('auth.otp.resend') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-military underline">
                        ارسال مجدد کد
                    </button>
                </form>
            </template>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('auth.otp.phone') }}" class="text-xs text-gray-400 hover:text-gray-600">
                ← تغییر شماره موبایل
            </a>
        </div>
    </div>
</div>
@endsection
```

---

## ۱۴. Tailwind Config

```js
// tailwind.config.js
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                military: {
                    dark:    '#1e2b16',
                    DEFAULT: '#3d5229',
                    light:   '#4f6b35',
                },
                cream: {
                    dark:    '#e8e0d0',
                    DEFAULT: '#f5f0e8',
                    light:   '#faf7f2',
                },
            },
            fontFamily: {
                sans: ['Vazirmatn', 'Tahoma', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
```

---

## ۱۵. Commands

### `app/Console/Commands/ExpireAds.php`

```php
class ExpireAds extends Command
{
    protected $signature   = 'ads:expire';
    protected $description = 'غیرفعال کردن آگهی‌های منقضی‌شده';

    public function handle(): void
    {
        $count = Ad::approved()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);

        $this->info("{$count} آگهی منقضی شد.");
    }
}
```

### `routes/console.php`

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('ads:expire')->dailyAt('00:00');
```

---

## ۱۶. ترتیب پیاده‌سازی برای Cursor

```
مرحله ۱: پایه
  □ نصب Laravel + پکیج‌ها
  □ .env پیکربندی
  □ تمام Migrations
  □ تمام Models
  □ Seeders (اجرا: php artisan migrate --seed)

مرحله ۲: احراز هویت
  □ SmsIrService
  □ OtpController (phone, send, verify, resend, logout)
  □ Views: auth/phone.blade.php, auth/verify.blade.php
  □ Routes auth

مرحله ۳: آگهی‌ها
  □ AdController (index, show, create, store, edit, update, destroy, myAds)
  □ StoreAdRequest + UpdateAdRequest
  □ Views: ads/index, show, create, edit, my-ads
  □ Components: ad-card, filter-sidebar, phone-badge
  □ AJAX routes (cities, branches)

مرحله ۴: Admin
  □ Admin model + migration
  □ AdminSeeder
  □ Filament install + AdResource
  □ php artisan make:filament-user

مرحله ۵: Telegram
  □ SendAdToTelegramJob
  □ TelegramWebhookController
  □ Webhook route + تنظیم webhook در تلگرام:
    GET https://api.telegram.org/bot{TOKEN}/setWebhook?url={APP_URL}/telegram/webhook/{SECRET}

مرحله ۶: Queue
  □ php artisan queue:table && php artisan migrate
  □ ExpireAds command
  □ Schedule

مرحله ۷: UI Polish
  □ فارسی‌سازی Pagination: php artisan vendor:publish --tag=laravel-pagination
  □ lang/fa/ برای validation messages
  □ Responsive test موبایل
  □ Empty states
  □ Toast notifications
```

---

## نکات نهایی

- برای تبدیل تاریخ به شمسی از `\Morilog\Jalali\Jalalian::fromCarbon($date)->format('j F Y')` استفاده کن
- Filament panel route پیش‌فرض `/admin` است — guard را به `Admin` model وصل کن
- برای webhook تلگرام، یک `TELEGRAM_WEBHOOK_SECRET` تصادفی در env تعریف کن و در route چک کن
- آدرس webhook ثبت: `https://api.telegram.org/bot{TOKEN}/setWebhook?url=https://yourdomain.com/telegram/webhook/{SECRET}`
- برای production حتماً `php artisan config:cache && php artisan route:cache` اجرا کن
```

---

## ۱۷. معماری پروژه (وضعیت فعلی)

پوتینو یک اپلیکیشن **تک‌صفحه‌ای (SPA-like listing)** با بک‌اند Laravel است که آگهی‌های تبادل محل خدمت سربازی را مدیریت می‌کند. فرانت‌اند Blade + Alpine.js + Tailwind CSS v4 است و بدون Livewire پیاده‌سازی شده.

### نمای کلی لایه‌ها

```
┌─────────────────────────────────────────────────────────────┐
│  Browser (RTL, فارسی)                                       │
│  Blade Views + Alpine.js + Tom Select                       │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│  routes/web.php                                             │
│  ├── AdController        (عمومی + auth)                     │
│  ├── OtpController       (احراز هویت OTP)                     │
│  ├── AdminController     (پنل ادمین سبک)                      │
│  └── TelegramWebhookController                              │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│  Services                                                   │
│  ├── SmsIrService          ارسال OTP                        │
│  └── ShahkarKycService     احراز کد ملی ↔ موبایل (Finnotech) │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│  Models + MySQL/SQLite                                      │
│  User, Ad, Otp, Province, City, Rank, ...                   │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│  Queue Jobs                                                 │
│  SendAdToTelegramJob  →  اطلاع‌رسانی آگهی جدید به تلگرام      │
└─────────────────────────────────────────────────────────────┘
```

### ساختار پوشه‌های کلیدی

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AdController.php
│   │   ├── Auth/OtpController.php
│   │   ├── Admin/AdminController.php
│   │   └── TelegramWebhookController.php
│   ├── Middleware/AdminAccess.php
│   └── Requests/StoreAdRequest.php, UpdateAdRequest.php
├── Models/          User, Ad, Otp, Province, City, ...
├── Services/        SmsIrService, ShahkarKycService
└── Jobs/            SendAdToTelegramJob

resources/
├── views/
│   ├── layouts/     app.blade.php, auth.blade.php
│   ├── ads/         index, show, create, form, my-ads
│   ├── auth/        phone, verify, complete-profile
│   ├── admin/       index
│   └── components/  ad-card, empty-state
├── css/app.css      Tailwind v4 + IRANYekanXVF
└── js/app.js        Alpine + Tom Select

public/
├── img/
│   ├── Untitled.png       لوگوی هدر و فرم‌های ورود
│   └── logo-pootino.png   favicon
└── iranuekanxpro/         فونت IRANYekanXVF
```

### جریان احراز هویت (دو مرحله‌ای)

احراز هویت به‌صورت **Phone-first** پیاده‌سازی شده؛ نام و کد ملی در ابتدا گرفته نمی‌شود.

```
کاربر مهمان
    │
    ▼
/auth/phone  ──►  فقط شماره موبایل
    │
    ▼
sendOtp  ──►  تولید OTP + (SMS در production)
    │
    ▼
/auth/verify  ──►  وارد کردن کد ۶ رقمی
    │
    ├── کاربر موجود با national_code  ──►  لاگین ──►  صفحه اصلی
    │
    └── کاربر جدید / بدون کد ملی
              │
              ▼
        /auth/complete-profile
              │
              ├── نام و نام خانوادگی
              ├── کد ملی
              └── Shahkar KYC (در local بای‌پس)
              │
              ▼
        ذخیره پروفایل ──►  صفحه اصلی
```

**فایل‌های مرتبط:** `OtpController`, `auth/phone.blade.php`, `auth/verify.blade.php`, `auth/complete-profile.blade.php`

### جریان آگهی

| مرحله | مسیر | توضیح |
|--------|------|--------|
| لیست | `GET /` | فیلتر + جستجوی لایو |
| جزئیات | `GET /ads/{ad}` | فقط آگهی‌های `approved` و `is_active` |
| ثبت | `GET /ads/create` | نیاز به `auth` — **قبل از** روت `ads/{ad}` تعریف شده |
| ویرایش | `GET /ads/{ad}/edit` | فقط مالک آگهی |
| آگهی‌های من | `GET /my-ads` | لیست آگهی‌های کاربر |

**وضعیت آگهی:** `pending` → (تایید ادمین) → `approved` | `rejected`

### پنل ادمین (سبک، بدون Filament)

پنل ادمین فعلاً یک **CRUD سبک تایید/رد** است، نه Filament:

- مسیر: `/admin`
- میدلور: `auth` + `admin` (`AdminAccess`)
- دسترسی: شماره موبایل کاربر باید در `ADMIN_PHONES` (env) باشد
- عملیات: لیست آگهی‌ها، فیلتر وضعیت، تایید، رد (با `admin_note`)

```env
ADMIN_PHONES=09123456789,09111111111
```

---

## ۱۸. تغییرات پیاده‌سازی‌شده (Changelog)

### احراز هویت

- [x] ورود دو مرحله‌ای: ابتدا موبایل + OTP، سپس تکمیل پروفایل برای کاربر جدید
- [x] در `APP_ENV=local`: بای‌پس Shahkar و SMS؛ نمایش کد OTP روی صفحه verify برای تست
- [x] OTP در سشن با کلید `otp_preview_code` (فقط local)

### UI / UX

- [x] **هدر:** لوگو (`Untitled.png`)، منوی دسکتاپ و همبرگری **فقط برای کاربر لاگین**
- [x] **منوی موبایل:** آگهی‌ها، آگهی‌های من، ثبت آگهی، پنل ادمین (در صورت مجوز)، خروج
- [x] **دکمه خروج** در دسکتاپ با استایل واضح (border قرمز)
- [x] **فاوآیکون** از `public/img/logo-pootino.png`
- [x] **فونت:** IRANYekanXVF (حذف Vazirmatn و CDN bunny)
- [x] **صفحه آگهی:** نمایش شماره تماس با دکمه «کپی شماره» (Alpine + clipboard API)
- [x] **هشدار ورود:** باکس وسط‌چین با استایل amber برای مهمان‌ها
- [x] **کارت آگهی:** کل کارت قابل کلیک (لینک overlay) + دکمه مشاهده
- [x] **جستجو:** لایو با debounce ۳۵۰ms روی صفحه اصلی (بدون نیاز به Enter)
- [x] **فرم ثبت آگهی:** شماره تماس readonly از حساب کاربر؛ استایل واضح input/textarea

### SEO

متا‌تگ‌های فنی در `layouts/app.blade.php`:

- `description`, `robots`, `canonical`
- Open Graph (`og:title`, `og:description`, `og:image`, ...)
- Twitter Card
- قابل override با `@section('meta_description')` در هر صفحه

### باگ‌فیکس‌ها

- [x] **404 ثبت آگهی:** روت `ads/create` قبل از `ads/{ad}` قرار گرفت تا Laravel کلمه `create` را به‌عنوان `{ad}` تفسیر نکند
- [x] **Blade syntax:** بستن صحیح `@auth/@endauth` در layout

### پیکربندی محیط توسعه (local)

```env
APP_ENV=local

# برای جلوگیری از خطای Connection refused روی sessions:
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

# یا MySQL را روشن نگه دار و migrate کن
```

> **نکته:** اگر `SESSION_DRIVER=database` باشد و MySQL در دسترس نباشد، هر درخواست (از جمله جستجوی لایو) با خطای `Connection refused` روی جدول `sessions` شکست می‌خورد.

---

## ۱۹. متغیرهای محیطی (به‌روز)

```env
APP_NAME=پوتینو
APP_ENV=local
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=pootino
DB_USERNAME=root
DB_PASSWORD=root

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database

SMSIR_API_KEY=
SMSIR_TEMPLATE_ID=

TELEGRAM_BOT_TOKEN=
TELEGRAM_ADMIN_CHAT_ID=
TELEGRAM_WEBHOOK_SECRET=

ADMIN_PHONES=09123456789

FINNOTECH_ADDRESS=https://sandboxapi.finnotech.ir
FINNOTECH_CLIENT_ID=
FINNOTECH_TOKEN=
```

---

## ۲۰. روت‌های فعلی (`routes/web.php`)

| Method | URI | Name | Middleware |
|--------|-----|------|------------|
| GET | `/` | `ads.index` | — |
| GET | `/ads/{ad}` | `ads.show` | — |
| GET | `/ads/create` | `ads.create` | auth |
| POST | `/ads` | `ads.store` | auth |
| GET | `/ads/{ad}/edit` | `ads.edit` | auth |
| PUT | `/ads/{ad}` | `ads.update` | auth |
| DELETE | `/ads/{ad}` | `ads.destroy` | auth |
| GET | `/my-ads` | `ads.my` | auth |
| GET | `/auth/phone` | `auth.otp.phone` | — |
| POST | `/auth/send-otp` | `auth.otp.send` | — |
| GET | `/auth/verify` | `auth.otp.verify-form` | — |
| POST | `/auth/verify` | `auth.otp.verify` | — |
| GET | `/auth/complete-profile` | `auth.otp.complete-profile-form` | auth |
| POST | `/auth/complete-profile` | `auth.otp.complete-profile` | auth |
| POST | `/auth/logout` | `auth.otp.logout` | — |
| GET | `/admin` | `admin.index` | auth, admin |
| POST | `/admin/ads/{ad}/approve` | `admin.ads.approve` | auth, admin |
| POST | `/admin/ads/{ad}/reject` | `admin.ads.reject` | auth, admin |

---

## ۲۱. کارهای باقی‌مانده (پیشنهادی)

- [ ] اجباری کردن `complete-profile` قبل از ثبت آگهی (middleware)
- [ ] پنل ادمین کامل‌تر (Filament یا داشبورد آمار)
- [ ] `schema.org` JSON-LD برای SEO
- [ ] `sitemap.xml` و `robots.txt`
- [ ] جستجوی AJAX بدون reload صفحه (فعلاً submit فرم با debounce)
- [ ] تبدیل `favicon.ico` از لوگو برای مرورگرهای قدیمی