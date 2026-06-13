<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class BlogTagValidator
{
    /** @var array<string, array<int, string>> */
    private array $errors = [];

    /**
     * @param array<string, mixed> $payload
     */
    public function validate(array $payload): bool
    {
        $validator = new Validator();

        $isValid = $validator->validate($payload, [
            'name' => 'required|max:190',
            'slug' => 'required|max:190',
            'description' => 'nullable|max:2000',
        ]);

        $this->errors = $validator->errors();
        return $isValid;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}