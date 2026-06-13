<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class BookingRequestValidator
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
            'inquiry_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Inquiry reference must be numeric.',
            ],
            'service_id' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Selected service must be numeric.',
            ],
            'first_name' => 'required|max:120',
            'last_name' => 'required|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|max:60',
            'requested_date' => 'required|date',
            'requested_time' => [
                'nullable',
                static function (mixed $value): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return preg_match('/^\d{2}:\d{2}$/', (string) $value) === 1 ? true : 'Requested time must use HH:MM format.';
                },
            ],
            'event_type' => 'required|max:150',
            'location' => 'required|max:190',
            'hours_needed' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || is_numeric($value)) ? true : 'Hours needed must be numeric.',
            ],
            'guest_count' => [
                'nullable',
                static fn (mixed $value): bool|string => ($value === null || $value === '' || ctype_digit((string) $value)) ? true : 'Guest count must be a whole number.',
            ],
            'notes' => 'nullable|max:4000',
            'status' => 'required|in:new,in_progress,quoted,confirmed,archived',
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