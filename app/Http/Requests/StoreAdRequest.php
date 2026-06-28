<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'current_province_id' => 'required|exists:provinces,id',
            'desired_province_id' => 'required|exists:provinces,id',
            'branch_type' => 'required|in:army,sepah,police',
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان آگهی الزامی است.',
            'current_province_id.required' => 'محل خدمت فعلی را انتخاب کنید.',
            'current_province_id.exists' => 'استان محل خدمت معتبر نیست.',
            'desired_province_id.required' => 'محل درخواستی را انتخاب کنید.',
            'desired_province_id.exists' => 'استان محل درخواستی معتبر نیست.',
            'branch_type.required' => 'ارگان را انتخاب کنید.',
            'branch_type.in' => 'ارگان انتخاب‌شده معتبر نیست.',
            'phone.regex' => 'شماره تماس معتبر نیست.',
        ];
    }
}
