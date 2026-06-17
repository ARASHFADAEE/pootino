<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdReport extends Model
{
    protected $fillable = ['ad_id', 'user_id', 'ip', 'reason', 'description'];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reasonLabel(): string
    {
        return match ($this->reason) {
            'fake' => 'اطلاعات نادرست',
            'duplicate' => 'آگهی تکراری',
            'spam' => 'اسپم',
            'other' => 'سایر',
        };
    }
}
