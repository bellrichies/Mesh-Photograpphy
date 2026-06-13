<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class HeroSlideValidator
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
            'subtitle' => 'nullable|max:190',
            'description' => 'nullable|max:4000',
            'image_media_id' => [
                'required',
                static fn (mixed $value): bool|string => ctype_digit((string) $value) ? true : 'Slide image must be a valid media identifier.',
            ],
            'image_alt_text' => 'nullable|max:255',
            'primary_cta_label' => 'nullable|max:120',
            'primary_cta_url' => [
                'nullable',
                static fn (mixed $value): bool|string => self::isValidLink($value) ? true : 'Primary CTA URL must be a valid absolute URL or relative path.',
            ],
            'secondary_cta_label' => 'nullable|max:120',
            'secondary_cta_url' => [
                'nullable',
                static fn (mixed $value): bool|string => self::isValidLink($value) ? true : 'Secondary CTA URL must be a valid absolute URL or relative path.',
            ],
            'sort_order' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || is_numeric($value)) ? true : 'Sort order must be numeric.',
            ],
            'status' => 'required|in:draft,published,archived',
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

    private static function isValidLink(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $url = trim((string) $value);
        if ($url === '') {
            return true;
        }

        if (str_starts_with($url, '/')) {
            return true;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
