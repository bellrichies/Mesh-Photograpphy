<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $validated = [];

    public function __construct(
        private readonly array $data,
        private readonly array $rules
    ) {
        $this->validate();
    }

    public static function make(array $data, array $rules): static
    {
        return new static($data, $rules);
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $rules = is_string($ruleString) ? explode('|', $ruleString) : (array) $ruleString;

            $nullable = in_array('nullable', $rules, true);

            if ($nullable && ($value === null || $value === '')) {
                $this->validated[$field] = $value;
                continue;
            }

            $fieldErrors = [];

            foreach ($rules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                if (is_callable($rule) && !is_string($rule)) {
                    $result = $rule($value, $this->data);
                    if ($result !== true) {
                        $fieldErrors[] = is_string($result) ? $result : "The {$field} field is invalid.";
                    }
                    continue;
                }

                $error = $this->applyRule($field, $value, $rule);
                if ($error) {
                    $fieldErrors[] = $error;
                }
            }

            if (empty($fieldErrors)) {
                $this->validated[$field] = $value;
            } else {
                $this->errors[$field] = $fieldErrors[0];
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): ?string
    {
        if (str_contains($rule, ':')) {
            [$name, $param] = explode(':', $rule, 2);
        } else {
            $name  = $rule;
            $param = null;
        }

        return match ($name) {
            'required' => ($value === null || $value === '')
                ? "The {$field} field is required."
                : null,

            'string' => ($value !== null && !is_string($value))
                ? "The {$field} must be a string."
                : null,

            'integer', 'int' => ($value !== null && !filter_var($value, FILTER_VALIDATE_INT))
                ? "The {$field} must be an integer."
                : null,

            'numeric' => ($value !== null && !is_numeric($value))
                ? "The {$field} must be numeric."
                : null,

            'boolean', 'bool' => ($value !== null && !is_bool($value) && !in_array($value, [0, 1, '0', '1'], true))
                ? "The {$field} must be boolean."
                : null,

            'email' => ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL))
                ? "The {$field} must be a valid email address."
                : null,

            'min' => ($value !== null && strlen((string) $value) < (int) $param)
                ? "The {$field} must be at least {$param} characters."
                : null,

            'max' => ($value !== null && strlen((string) $value) > (int) $param)
                ? "The {$field} may not be greater than {$param} characters."
                : null,

            'date' => ($value !== null && $value !== '' && !strtotime((string) $value))
                ? "The {$field} must be a valid date."
                : null,

            'in' => ($value !== null && $value !== '' && !in_array($value, explode(',', $param ?? ''), true))
                ? "The {$field} must be one of: {$param}."
                : null,

            'url' => ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL))
                ? "The {$field} must be a valid URL."
                : null,

            'array' => ($value !== null && !is_array($value))
                ? "The {$field} must be an array."
                : null,

            default => null,
        };
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        return $this->validated;
    }
}
