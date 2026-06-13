<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class TestimonialValidator
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
            'client_name' => 'required|max:190',
            'client_label' => 'nullable|max:190',
            'quote' => 'required|max:3000',
            'long_form_story' => 'nullable',
            'rating' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    $number = (int) $value;
                    return $number >= 1 && $number <= 5 ? true : 'Rating must be between 1 and 5.';
                },
            ],
            'featured' => 'required|in:0,1',
            'service_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Service link must be a valid numeric identifier.',
            ],
            'gallery_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Gallery link must be a valid numeric identifier.',
            ],
            'portrait_media_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Portrait media must be a valid numeric identifier.',
            ],
            'event_date' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return strtotime((string) $value) !== false ? true : 'Event date must be a valid date.';
                },
            ],
            'location' => 'nullable|max:190',
            'status' => 'required|in:draft,published,archived',
            'sort_order' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || is_numeric($value)) ? true : 'Sort order must be numeric.',
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