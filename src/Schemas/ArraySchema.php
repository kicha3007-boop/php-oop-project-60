<?php

namespace Hexlet\Validator\Schemas;

use Hexlet\Validator\CustomValidators;

class ArraySchema extends BaseSchema
{
    public function __construct(CustomValidators $customValidators)
    {
        parent::__construct($customValidators);
        $this->addRule('isArray', fn(mixed $value): bool => is_array($value));
    }

    public function sizeof(int $size): static
    {
        return $this->addRule('sizeof', fn(mixed $value): bool => is_array($value) && count($value) === $size);
    }

    /**
     * Каждый ключ проверяется своей схемой — той же, что и для отдельного значения.
     *
     * @param array<string, BaseSchema> $fields
     */
    public function shape(array $fields): static
    {
        return $this->addRule('shape', function (mixed $value) use ($fields): bool {
            foreach ($fields as $key => $schema) {
                if (!$schema->isValid(is_array($value) ? $value[$key] ?? null : null)) {
                    return false;
                }
            }

            return true;
        });
    }
}
