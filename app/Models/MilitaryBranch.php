<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilitaryBranch extends Model
{
    protected $fillable = ['type', 'name'];

    public function typeLabel(): string
    {
        return match ($this->type) {
            'army' => 'ارتش جمهوری اسلامی',
            'sepah' => 'سپاه پاسداران',
            'police' => 'نیروی انتظامی',
            default => $this->type,
        };
    }
}
