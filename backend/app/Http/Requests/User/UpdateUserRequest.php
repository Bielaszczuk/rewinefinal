<?php

namespace App\Http\Requests\User;

use App\Domain\User\DTOs\UpdateUserDto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();
        if (!$user) return false;
        return $user->id === $this->route('id') || $user->hasRole('ROLE_ADMIN');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:100', 'unique:users,username,' . $this->route('id')],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatarUrl' => ['nullable', 'url', 'max:500'],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function toDto(): UpdateUserDto
    {
        return new UpdateUserDto(
            name: $this->input('name'),
            username: $this->input('username'),
            phone: $this->input('phone'),
            avatarUrl: $this->input('avatarUrl'),
            password: $this->input('password')
        );
    }
}
