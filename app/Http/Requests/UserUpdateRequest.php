<?php

// app/Http/Requests/UserUpdateRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()?->is_admin ?? false; }
    public function rules(): array {
        $id = $this->route('user');
        return [
            'name'       => ['required','string','max:100'],
            'email'      => ['required','email','max:150', Rule::unique('users','email')->ignore($id)],
            'password'   => ['nullable','string','min:8'],
            'pin'        => ['required','string','max:20', Rule::unique('users','pin')->ignore($id)],
            'privilege'  => ['required','integer','in:0,1'],
            'fp_password'=> ['nullable','string','max:50'],
            'rfid'       => ['nullable','string','max:64'],
            'fp_template'=> ['nullable','string'],
        ];
    }
}
