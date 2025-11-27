<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // 5MB max
            'banner' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'], // 10MB max
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'avatar.max' => 'The profile picture must not be larger than 5MB. Please compress or resize your image.',
            'avatar.image' => 'The profile picture must be an image file (JPEG, PNG, GIF, or WebP).',
            'avatar.mimes' => 'The profile picture must be a JPEG, PNG, GIF, or WebP image.',
            'banner.max' => 'The banner image must not be larger than 10MB. Please compress or resize your image.',
            'banner.image' => 'The banner must be an image file (JPEG, PNG, GIF, or WebP).',
            'banner.mimes' => 'The banner must be a JPEG, PNG, GIF, or WebP image.',
        ];
    }
}
