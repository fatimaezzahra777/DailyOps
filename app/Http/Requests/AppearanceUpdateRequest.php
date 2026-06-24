<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppearanceUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'theme_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'font_size' => ['required', Rule::in(['small', 'normal', 'large'])],
        ];
    }

    public function messages(): array
    {
        return [
            'theme_color.regex' => 'Choisissez une couleur valide.',
            'font_size.in' => 'Choisissez une taille de texte valide.',
        ];
    }
}
