<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id','title','description','current_province_id','current_city_id','current_branch_id','unit_name','desired_province_id','desired_city_id','rank_id','education_level_id','phone','status','admin_note','approved_at','expires_at','is_active','views','edited_after_approval'];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime','expires_at' => 'datetime','is_active' => 'boolean','edited_after_approval' => 'boolean'];
    }

    public function scopeApproved($query)
    {
        return $query
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function user() { return $this->belongsTo(User::class); }
    public function currentProvince() { return $this->belongsTo(Province::class, 'current_province_id'); }
    public function currentCity() { return $this->belongsTo(City::class, 'current_city_id'); }
    public function currentBranch() { return $this->belongsTo(MilitaryBranch::class, 'current_branch_id'); }
    public function desiredProvince() { return $this->belongsTo(Province::class, 'desired_province_id'); }
    public function desiredCity() { return $this->belongsTo(City::class, 'desired_city_id'); }
    public function rank() { return $this->belongsTo(Rank::class); }
    public function educationLevel() { return $this->belongsTo(EducationLevel::class); }
    public function reports() { return $this->hasMany(AdReport::class); }
}
