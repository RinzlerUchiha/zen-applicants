<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * An answer set may only carry the assessment's own question keys — nothing
 * invented, so a hand-made request cannot store answers to questions that do
 * not exist. With $complete, every question must be answered as well.
 */
class KnownKeys implements ValidationRule
{
    private array $keys;

    public function __construct(array $keys, private bool $complete = false)
    {
        $this->keys = array_map('strval', $keys);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            return;
        }

        $given = array_map('strval', array_keys($value));

        if (array_diff($given, $this->keys)) {
            $fail('Invalid Input');
        } elseif ($this->complete && array_diff($this->keys, $given)) {
            $fail('Please answer every item');
        }
    }
}
