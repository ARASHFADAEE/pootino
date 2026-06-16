<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'current_province_id' => 'required|exists:provinces,id',
            'current_city_id' => 'required|exists:cities,id',
            'current_branch_id' => 'required|exists:military_branches,id',
            'desired_province_id' => 'required|exists:provinces,id',
            'desired_city_id' => 'required|exists:cities,id',
            'rank_id' => 'required|exists:ranks,id',
            'education_level_id' => 'required|exists:education_levels,id',
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
        ];
    }
}
