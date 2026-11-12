<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Input validation with error collection.
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];
    /** @var array<string,mixed> */
    private array $data;

    /** @param array<string,mixed> $data */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    private function value(string $field): mixed
    {
        $value = $this->data[$field] ?? null;
        return is_string($value) ? trim($value) : $value;
    }

    public function required(string $field, string $label): self
    {
        $value = $this->value($field);
        if ($value === null || $value === '' || (is_array($value) && $value === [])) {
            $this->addError($field, $label . ' is required.');
        }
        return $this;
    }

    public function email(string $field, string $label, bool $required = false): self
    {
        $value = (string) ($this->value($field) ?? '');
        if ($value === '') {
            if ($required) {
                $this->addError($field, $label . ' is required.');
            }
            return $this;
        }
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $label . ' must be a valid email address.');
        }
        return $this;
    }

    public function phone(string $field, string $label, bool $required = true): self
    {
        $value = (string) ($this->value($field) ?? '');
        if ($value === '') {
            if ($required) {
                $this->addError($field, $label . ' is required.');
            }
            return $this;
        }
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            $this->addError($field, $label . ' must be a valid phone number.');
        }
        return $this;
    }

    public function min(string $field, string $label, int $min): self
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && mb_strlen((string) $value) < $min) {
            $this->addError($field, sprintf('%s must be at least %d characters.', $label, $min));
        }
        return $this;
    }

    public function max(string $field, string $label, int $max): self
    {
        $value = $this->value($field);
        if ($value !== null && mb_strlen((string) $value) > $max) {
            $this->addError($field, sprintf('%s must not exceed %d characters.', $label, $max));
        }
        return $this;
    }

    public function numeric(string $field, string $label, bool $required = true): self
    {
        $value = $this->value($field);
        if (($value === null || $value === '') && !$required) {
            return $this;
        }
        if (!is_numeric($value)) {
            $this->addError($field, $label . ' must be a number.');
        }
        return $this;
    }

    public function minValue(string $field, string $label, float $min): self
    {
        $value = $this->value($field);
        if (is_numeric($value) && (float) $value < $min) {
            $this->addError($field, sprintf('%s must be at least %s.', $label, $min));
        }
        return $this;
    }

    public function integer(string $field, string $label, bool $required = true, ?int $min = null): self
    {
        $value = $this->value($field);
        if (($value === null || $value === '') && !$required) {
            return $this;
        }
        if (!preg_match('/^-?\d+$/', (string) $value)) {
            $this->addError($field, $label . ' must be a whole number.');
            return $this;
        }
        if ($min !== null && (int) $value < $min) {
            $this->addError($field, sprintf('%s must be at least %d.', $label, $min));
        }
        return $this;
    }

    public function in(string $field, string $label, array $allowed): self
    {
        $value = $this->value($field);
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->addError($field, $label . ' has an invalid value.');
        }
        return $this;
    }

    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors === [] ? null : (string) reset($this->errors);
    }
}
