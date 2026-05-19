<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => 'required|in:available,pending,sold',
            'category_name' => 'nullable|string|max:255',
            'photo_url' => 'nullable|url',
            'tags' => 'nullable|string|max:500',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Pole nazwa jest wymagane.',
            'name.string' => 'Pole nazwa musi być tekstem.',
            'name.max' => 'Pole nazwa może mieć maksymalnie 255 znaków.',

            'status.required' => 'Pole status jest wymagane.',
            'status.in' => 'Pole status musi mieć jedną z wartości: available, pending, sold.',

            'category_name.string' => 'Pole nazwa kategorii musi być tekstem.',
            'category_name.max' => 'Pole nazwa kategorii może mieć maksymalnie 255 znaków.',

            'photo_url.url' => 'Pole URL zdjęcia musi być poprawnym adresem URL.',

            'tags.string' => 'Pole tagi musi być tekstem.',
            'tags.max' => 'Pole tagi może mieć maksymalnie 500 znaków.',
        ];
    }
}

