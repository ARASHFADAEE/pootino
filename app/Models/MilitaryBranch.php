<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilitaryBranch extends Model
{
    protected $fillable = ['organization_id', 'name'];

    public function organization()
    {
        return $this->belongsTo(MilitaryOrganization::class, 'organization_id');
    }
}
