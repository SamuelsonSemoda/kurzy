<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $participant = $this->route('participant');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('participants', 'email')->ignore($participant)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Zadejte jméno účastníka.',
            'email.required' => 'Zadejte e-mail.',
            'email.email' => 'E-mail nemá platný formát.',
            'email.unique' => 'Účastník s tímto e-mailem už existuje.',
        ];
    }
}
