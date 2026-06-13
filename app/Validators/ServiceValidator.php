<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class ServiceValidator
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
            'title' => 'required|max:190',
            'slug' => 'required|max:190',
            'short_description' => 'nullable|max:1600',
            'full_description' => 'nullable',
            'cover_media_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Cover media must be a valid numeric identifier.',
            ],
            'sort_order' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || is_numeric($value)) ? true : 'Sort order must be numeric.',
            ],
            'featured' => 'required|in:0,1',
            'status' => 'required|in:draft,published,archived',
            'price_display' => 'nullable|max:190',
            'meta_title' => 'nullable|max:255',
            'meta_description' => 'nullable|max:2000',
            'og_title' => 'nullable|max:255',
            'og_description' => 'nullable|max:2000',
            'og_image_media_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'OG image must be a valid numeric identifier.',
            ],
            'canonical_url' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return filter_var((string) $value, FILTER_VALIDATE_URL) !== false ? true : 'Canonical URL must be a valid URL.';
                },
            ],
            'robots_index' => 'required|in:0,1',
            'robots_follow' => 'required|in:0,1',
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