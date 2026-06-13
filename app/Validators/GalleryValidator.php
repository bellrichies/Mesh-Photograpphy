<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class GalleryValidator
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
            'excerpt' => 'nullable|max:1200',
            'story_intro' => 'nullable|max:4000',
            'category_primary_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Primary category must be a valid numeric identifier.',
            ],
            'cover_media_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Cover media must be a valid numeric identifier.',
            ],
            'featured' => 'required|in:0,1',
            'status' => 'required|in:draft,published,archived',
            'location' => 'nullable|max:190',
            'event_date' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return strtotime((string) $value) !== false ? true : 'Event date must be a valid date.';
                },
            ],
            'client_name' => 'nullable|max:190',
            'sort_order' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || is_numeric($value)) ? true : 'Sort order must be numeric.',
            ],
            'published_at' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return strtotime((string) $value) !== false ? true : 'Published date must be a valid date/time.';
                },
            ],
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