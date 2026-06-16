<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilitaryOrganization extends Model
{
    protected $fillable = ['name'];

    public function branches()
    {
        return $this->hasMany(MilitaryBranch::class, 'organization_id');
    }
}
