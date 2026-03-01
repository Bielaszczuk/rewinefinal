<?php

namespace App\Domain\User\DTOs;

readonly class CreateUserDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $username = null,
        public ?string $phone = null,
        public ?string $avatarUrl = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'username' => $this->username,
            'phone' => $this->phone,
            'avatar_url' => $this->avatarUrl,
        ];
    }
}
