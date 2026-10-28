<?php

namespace Hexlet\Validator;

use Hexlet\Validator\Schemas\ArraySchema;
use Hexlet\Validator\Schemas\BaseSchema;
use Hexlet\Validator\Schemas\NumberSchema;
use Hexlet\Validator\Schemas\StringSchema;
use InvalidArgumentException;

/**
 * Фабрика схем и реестр пользовательских правил по паре «тип и имя».
 */
final class Validator
{
    /**
     * Классы схем по типу: новый тип — новая строка здесь и свой класс, базовый класс не меняется.
     *
     * @var array<string, class-string<BaseSchema>>
     */
    private const SCHEMAS = [
        'string' => StringSchema::class,
        'number' => NumberSchema::class,
        'array' => ArraySchema::class,
    ];

    /** @var array<string, CustomValidators> */
    private array $customValidators = [];

    public function __construct()
    {
        foreach (array_keys(self::SCHEMAS) as $type) {
            $this->customValidators[$type] = new CustomValidators();
        }
    }

    public function string(): StringSchema
    {
        return new StringSchema($this->customValidators['string']);
    }

    public function number(): NumberSchema
    {
        return new NumberSchema($this->customValidators['number']);
    }

    public function array(): ArraySchema
    {
        return new ArraySchema($this->customValidators['array']);
    }

    public function addValidator(string $type, string $name, callable $fn): void
    {
        if (!array_key_exists($type, $this->customValidators)) {
            throw new InvalidArgumentException("Unknown schema type \"{$type}\"");
        }

        $this->customValidators[$type]->add($name, $fn);
    }
}
