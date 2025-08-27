<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название задачи обязательно для заполнения',
            'title.max' => 'Название не должно превышать 255 символов',
            'description.max' => 'Описание не должно превышать 1000 символов',
            'tags.*.max' => 'Каждый тег не должен превышать 50 символов',
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->has('tags') && is_string($this->tags)) {
            $tags = array_filter(
                array_map('trim', explode(',', $this->tags)),
                fn($tag) => !empty($tag)
            );

            $this->merge([
                'tags' => $tags,
            ]);
        }
    }
}
