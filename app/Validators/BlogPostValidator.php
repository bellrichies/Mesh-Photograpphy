<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class BlogPostValidator
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
            'author_id' => [
                'required',
                static fn (mixed $value): bool|string => ctype_digit((string) $value) ? true : 'Author must be a valid numeric identifier.',
            ],
            'title' => 'required|max:190',
            'slug' => 'required|max:190',
            'excerpt' => 'nullable|max:4000',
            'body_long' => 'nullable',
            'featured_image_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Featured image must be a valid numeric identifier.',
            ],
            'cover_gallery_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Cover gallery must be a valid numeric identifier.',
            ],
            'status' => 'required|in:draft,published,scheduled,archived',
            'visibility' => 'required|in:public,unlisted,private',
            'is_featured' => 'required|in:0,1',
            'allow_comments' => 'required|in:0,1',
            'reading_time' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || is_numeric($value)) ? true : 'Reading time must be numeric.',
            ],
            'meta_summary' => 'nullable|max:2000',
            'canonical_url' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return filter_var((string) $value, FILTER_VALIDATE_URL) !== false ? true : 'Canonical URL must be a valid URL.';
                },
            ],
            'meta_title' => 'nullable|max:255',
            'meta_description' => 'nullable|max:2000',
            'og_title' => 'nullable|max:255',
            'og_description' => 'nullable|max:2000',
            'og_image_media_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'OG image must be a valid numeric identifier.',
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