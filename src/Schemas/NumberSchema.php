<?php

namespace Hexlet\Validator\Schemas;

class NumberSchema extends BaseSchema
{
    public function positive(): static
    {
        return $this->addRule('positive', fn(mixed $value): bool => $value > 0);
    }

    /**
     * Число в диапазоне, границы включительно.
     */
    public function range(int|float $min, int|float $max): static
    {
        return $this->addRule('range', fn(mixed $value): bool => $value >= $min && $value <= $max);
    }
}
