<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request untuk update profil pengguna (nama, email, username, password).
 * Password bersifat opsional — hanya diupdate jika diisi.
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = auth()->id();
        return [
            'name'                  => 'sometimes|required|string|max:255',
            'username'              => ['sometimes','required','string','max:255', Rule::unique('users')->ignore($userId)],
            'email'                 => ['sometimes','required','email', Rule::unique('users')->ignore($userId)],
            'password'              => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ];
    }
}
