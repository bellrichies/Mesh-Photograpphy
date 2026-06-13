<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    /** @var array<string, array<int, string>> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $validated = [];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string|array<int, mixed>> $rules
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        $this->validated = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $parsedRules = $this->parseRules($ruleSet);
            $nullable = in_array('nullable', $parsedRules, true);

            if ($nullable && ($value === null || $value === '')) {
                $this->validated[$field] = $value;
                continue;
            }

            foreach ($parsedRules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                if (is_callable($rule)) {
                    $result = $rule($value, $data);
                    if ($result !== true) {
                        $this->addError($field, is_string($result) ? $result : 'Invalid value.');
                    }
                    continue;
                }

                [$name, $parameter] = array_pad(explode(':', (string) $rule, 2), 2, null);
                $this->applyRule($field, $value, $name, $parameter);
            }

            if (! isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }

        return $this->errors === [];
    }

    /** @return array<string, array<int, string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        return $this->validated;
    }

    /**
     * @param string|array<int, mixed> $ruleSet
     * @return array<int, mixed>
     */
    private function parseRules(string|array $ruleSet): array
    {
        if (is_array($ruleSet)) {
            return $ruleSet;
        }

        return explode('|', $ruleSet);
    }

    private function applyRule(string $field, mixed $value, string $name, ?string $parameter): void
    {
        switch ($name) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, 'This field is required.');
                }
                break;

            case 'email':
                if (! is_string($value) || ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Please enter a valid email address.');
                }
                break;

            case 'min':
                $min = (int) $parameter;
                if (is_string($value) && mb_strlen($value) < $min) {
                    $this->addError($field, 'Minimum length is ' . $min . '.');
                }
                break;

            case 'max':
                $max = (int) $parameter;
                if (is_string($value) && mb_strlen($value) > $max) {
                    $this->addError($field, 'Maximum length is ' . $max . '.');
                }
                break;

            case 'date':
                if (strtotime((string) $value) === false) {
                    $this->addError($field, 'Please provide a valid date.');
                }
                break;

            case 'in':
                $allowed = $parameter !== null ? explode(',', $parameter) : [];
                if (! in_array((string) $value, $allowed, true)) {
                    $this->addError($field, 'Invalid selection.');
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
