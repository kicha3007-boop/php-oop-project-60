<?php

namespace Hexlet\Validator\Schemas;

class StringSchema extends BaseSchema
{
    public function minLength(int $length): static
    {
        return $this->addRule('minLength', fn(mixed $value): bool => mb_strlen((string) $value) >= $length);
    }

    public function contains(string $substring): static
    {
        return $this->addRule('contains', fn(mixed $value): bool => str_contains((string) $value, $substring));
    }

    /**
     * Для строки пустая строка — тоже пустое значение.
     */
    protected function isEmpty(mixed $value): bool
    {
        return parent::isEmpty($value) || $value === '';
    }
}
