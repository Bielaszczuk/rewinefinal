<?php

namespace App\Domain\User\DTOs;

readonly class UpdateUserDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $username = null,
        public ?string $phone = null,
        public ?string $avatarUrl = null,
        public ?string $password = null,
    ) {}

    public function toArray(): array
    {
        $data = array_filter([
            'name' => $this->name,
            'username' => $this->username,
            'phone' => $this->phone,
            'avatar_url' => $this->avatarUrl,
        ], fn($value) => $value !== null);

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        return $data;
    }
}
