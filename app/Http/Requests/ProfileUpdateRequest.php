<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'admin_title' => ['nullable', 'string', 'max:80'],
            'admin_bio' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:32'],
            'timezone' => ['nullable', 'timezone'],
            'theme' => ['nullable', 'in:default,dark,contrast'],
            'preferred_language' => ['nullable', 'in:bn,en'],
            'signature' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
