<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Route middleware owns authentication/role checks.
    public function rules(): array
    {
        return [
            'confirmation' => ['accepted'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:3000'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
