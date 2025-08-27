<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'completed' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название задачи обязательно для заполнения',
            'title.max' => 'Название не должно превышать 255 символов',
            'description.max' => 'Описание не должно превышать 1000 символов',
            'tags.*.max' => 'Каждый тег не должен превышать 50 символов',
            'completed.boolean' => 'Статус выполнения должен быть true или false',
        ];
    }

    public function prepareForValidation(): void
    {
        // Преобразуем строку тегов в массив, если пришла строка
        if ($this->has('tags') && is_string($this->tags)) {
            $tags = array_filter(
                array_map('trim', explode(',', $this->tags)),
                fn($tag) => !empty($tag)
            );

            $this->merge([
                'tags' => $tags,
            ]);
        }

        // Преобразуем completed в boolean
        if ($this->has('completed')) {
            $this->merge([
                'completed' => (bool) $this->completed,
            ]);
        }
    }
}
