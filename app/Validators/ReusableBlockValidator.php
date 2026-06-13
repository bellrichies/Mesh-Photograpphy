<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class ReusableBlockValidator
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $errors = [];

    /**
     * @param array<string, mixed> $payload
     */
    public function validate(array $payload): bool
    {
        $validator = new Validator();

        $isValid = $validator->validate($payload, [
            'name' => 'required|max:190',
            'block_key' => 'required|max:190',
            'block_type' => 'required|max:120',
            'title' => 'nullable|max:255',
            'body' => 'nullable',
            'json_payload' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    json_decode((string) $value, true);
                    return json_last_error() === JSON_ERROR_NONE ? true : 'JSON payload must be valid JSON.';
                },
            ],
            'status' => 'required|in:draft,published,hidden',
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