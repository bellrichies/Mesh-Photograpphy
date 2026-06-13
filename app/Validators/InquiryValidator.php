<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\Validator;

class InquiryValidator
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
            'first_name' => 'required|max:120',
            'last_name' => 'required|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|max:60',
            'company_name' => 'nullable|max:190',
            'service_interest' => 'nullable|max:190',
            'preferred_date' => 'nullable|date',
            'budget_range' => 'nullable|max:120',
            'location' => 'nullable|max:190',
            'referral_source' => 'nullable|max:190',
            'message' => 'required|max:4000',
            'status' => 'required|in:new,in_progress,responded,archived,spam',
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