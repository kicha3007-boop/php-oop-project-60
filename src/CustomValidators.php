<?php

namespace Hexlet\Validator;

/**
 * Пользовательские правила одного типа схемы по имени.
 */
final class CustomValidators
{
    /** @var array<string, callable> */
    private array $validators = [];

    public function add(string $name, callable $fn): void
    {
        $this->validators[$name] = $fn;
    }

    public function get(string $name): ?callable
    {
        return $this->validators[$name] ?? null;
    }
}
