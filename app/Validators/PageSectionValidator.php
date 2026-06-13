<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;
use App\Services\PageSectionService;

class PageSectionValidator
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
            'section_key' => 'required|max:190',
            'section_type' => 'required|in:' . implode(',', PageSectionService::TYPES),
            'title' => 'nullable|max:255',
            'subtitle' => 'nullable|max:255',
            'body' => 'nullable',
            'cta_label' => 'nullable|max:120',
            'cta_url' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return filter_var((string) $value, FILTER_VALIDATE_URL) !== false ? true : 'CTA URL must be a valid URL.';
                },
            ],
            'media_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Media selection is invalid.',
            ],
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
            'sort_order' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || is_numeric($value)) ? true : 'Sort order must be numeric.',
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