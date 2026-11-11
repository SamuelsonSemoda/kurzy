<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Course|null $course */
        $course = $this->route('course');

        // Při úpravě nelze snížit kapacitu pod počet již obsazených míst.
        $minCapacity = max(1, $course?->occupiedCount() ?? 1);

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->ignore($course)],
            'capacity' => ['required', 'integer', 'min:'.$minCapacity, 'max:100000'],
            'starts_at' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Zadejte název kurzu.',
            'code.required' => 'Zadejte kód kurzu.',
            'code.unique' => 'Kurz s tímto kódem už existuje.',
            'capacity.required' => 'Zadejte kapacitu kurzu.',
            'capacity.integer' => 'Kapacita musí být celé číslo.',
            'capacity.min' => 'Kapacita musí být alespoň :min (nelze jít pod počet obsazených míst a pod 1).',
            'capacity.max' => 'Kapacita může být nejvýše :max.',
            'starts_at.required' => 'Zadejte termín zahájení.',
            'starts_at.date' => 'Termín zahájení není platné datum.',
        ];
    }
}
