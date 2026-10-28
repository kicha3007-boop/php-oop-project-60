<?php

namespace Hexlet\Validator\Schemas;

use Closure;
use Hexlet\Validator\CustomValidators;
use InvalidArgumentException;

/**
 * Общая часть всех схем: правила хранятся по имени, обязательность, запуск проверки.
 */
abstract class BaseSchema
{
    /** @var array<string, Closure(mixed): bool> */
    private array $rules = [];

    private bool $isRequired = false;

    /**
     * Реестр пользовательских правил своего типа. Читается при вызове test(),
     * поэтому правило, добавленное после создания схемы, тоже доступно.
     */
    public function __construct(private readonly CustomValidators $customValidators)
    {
    }

    public function required(): static
    {
        $this->isRequired = true;
        return $this;
    }

    /**
     * Подключает правило, добавленное через Validator::addValidator().
     */
    public function test(string $name, mixed ...$args): static
    {
        $validate = $this->customValidators->get($name);
        if ($validate === null) {
            throw new InvalidArgumentException("Unknown validator \"{$name}\"");
        }

        return $this->addRule($name, fn(mixed $value): bool => (bool) $validate($value, ...$args));
    }

    public function isValid(mixed $value): bool
    {
        if ($this->isEmpty($value)) {
            return !$this->isRequired;
        }

        foreach ($this->rules as $check) {
            if (!$check($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Правило с тем же именем заменяет прежнее: последний вызов имеет приоритет.
     *
     * @param Closure(mixed): bool $check
     */
    protected function addRule(string $name, Closure $check): static
    {
        $this->rules[$name] = $check;
        return $this;
    }

    /**
     * Пустое значение проходит без правил, если схема не обязательная.
     */
    protected function isEmpty(mixed $value): bool
    {
        return $value === null;
    }
}
