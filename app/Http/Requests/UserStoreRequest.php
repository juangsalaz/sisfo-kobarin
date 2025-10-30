<?php

// app/Http/Requests/UserStoreRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()?->is_admin ?? false; }
    public function rules(): array {
        return [
            'name'       => ['required','string','max:100'],
            'no_hp'      => ['nullable','string'],
            'pin'        => ['required','string','max:20','unique:users,pin'],
            'jenis_kelamin' => ['required'],
            'kategori' => ['required'],
        ];
    }
}
