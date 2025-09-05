<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // sesuaikan kalau kamu pakai gate/policy; sementara true
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id ?? $this->route('id');

        return [
            'name'        => ['required','string','max:255'],
            'no_hp'       => ['nullable','string','max:255'],
            'is_admin'    => ['sometimes','boolean'],
            'pin'         => ['required','string','max:255', Rule::unique('users','pin')->ignore($id)]
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_admin' => (bool) $this->boolean('is_admin')
        ]);
    }
}
