<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $phone = en_digits($this->input('phone'));

        if ($phone === '' && $this->user()) {
            $phone = $this->user()->phone;
        }

        $this->merge(['phone' => $phone]);
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
            'branch_type.required' => 'لطفاً ارگان خود را انتخاب کنید.',
            'branch_type.in' => 'ارگان انتخاب‌شده معتبر نیست.',
            'phone.required' => 'شماره تماس الزامی است.',
            'phone.regex' => 'شماره تماس معتبر نیست.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'عنوان آگهی',
            'current_province_id' => 'محل خدمت فعلی',
            'desired_province_id' => 'محل درخواستی',
            'branch_type' => 'ارگان',
            'phone' => 'شماره تماس',
            'description' => 'توضیحات',
        ];
    }
}
