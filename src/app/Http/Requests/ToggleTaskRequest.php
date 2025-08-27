<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'completed' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'completed.boolean' => 'Статус выполнения должен быть true или false',
        ];
    }
}
