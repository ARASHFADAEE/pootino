<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'is_active'];

    protected $hidden = ['remember_token'];

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }
}
