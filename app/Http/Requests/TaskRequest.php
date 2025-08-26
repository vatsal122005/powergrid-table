<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The Title must be a string.',
            'description.required' => 'The description field is required.',
            'description.string' => 'The Description must be a string.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The Status must be pending, in_progress or completed.',
            'due_date.required' => 'The due date field is required.',
            'due_date.date' => 'The Due Date must be a date.',
            'user_id.required' => 'The User ID field is required.',
            'user_id.exists' => 'The User ID does not exist.',
        ];
    }
}
