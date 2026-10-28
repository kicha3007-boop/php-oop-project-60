<?php

namespace Hexlet\Validator\Tests;

use Hexlet\Validator\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private Validator $v;

    protected function setUp(): void
    {
        $this->v = new Validator();
    }

    /** @return array<string, array{mixed, bool}> */
    public static function requiredStringProvider(): array
    {
        return [
            'непустая строка' => ['hexlet', true],
            'пустая строка' => ['', false],
            'null' => [null, false],
        ];
    }

    public function testStringIsOptionalByDefault(): void
    {
        $schema = $this->v->string();

        $this->assertTrue($schema->isValid(''));
        $this->assertTrue($schema->isValid(null));
        $this->assertTrue($schema->isValid('hexlet'));
    }

    #[DataProvider('requiredStringProvider')]
    public function testStringRequired(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, $this->v->string()->required()->isValid($value));
    }

    public function testStringContains(): void
    {
        $this->assertTrue($this->v->string()->contains('what')->isValid('what does the fox say'));
        $this->assertFalse($this->v->string()->contains('whatthe')->isValid('what does the fox say'));
    }

    public function testStringMinLengthIncludesBorder(): void
    {
        $this->assertTrue($this->v->string()->minLength(6)->isValid('hexlet'));
        $this->assertFalse($this->v->string()->minLength(7)->isValid('hexlet'));
    }

    public function testRepeatedRuleReplacesPrevious(): void
    {
        $this->assertTrue($this->v->string()->minLength(10)->minLength(4)->isValid('hexlet'));
        $this->assertFalse($this->v->string()->minLength(4)->minLength(10)->isValid('hexlet'));
    }

    public function testSchemasAreIndependent(): void
    {
        $schema = $this->v->string();
        $other = $this->v->string();
        $schema->required();

        $this->assertTrue($other->isValid(''));
        $this->assertFalse($schema->isValid(''));
    }

    /** @return array<string, array{mixed, bool}> */
    public static function positiveProvider(): array
    {
        return [
            'положительное' => [10, true],
            'ноль' => [0, false],
            'отрицательное' => [-10, false],
            'null без required' => [null, true],
        ];
    }

    #[DataProvider('positiveProvider')]
    public function testNumberPositive(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, $this->v->number()->positive()->isValid($value));
    }

    public function testNumberRequiredAcceptsZero(): void
    {
        $schema = $this->v->number()->required();

        $this->assertTrue($schema->isValid(0));
        $this->assertFalse($schema->isValid(null));
    }

    /** @return array<string, array{int, bool}> */
    public static function rangeProvider(): array
    {
        return [
            'нижняя граница' => [-5, true],
            'верхняя граница' => [5, true],
            'внутри' => [0, true],
            'ниже' => [-6, false],
            'выше' => [6, false],
        ];
    }

    #[DataProvider('rangeProvider')]
    public function testNumberRangeIncludesBorders(int $value, bool $expected): void
    {
        $this->assertSame($expected, $this->v->number()->range(-5, 5)->isValid($value));
    }

    public function testArrayRequired(): void
    {
        $schema = $this->v->array();
        $this->assertTrue($schema->isValid(null));

        $schema->required();
        $this->assertFalse($schema->isValid(null));
        $this->assertFalse($schema->isValid('hexlet'));
        $this->assertTrue($schema->isValid([]));
    }

    public function testArraySizeof(): void
    {
        $schema = $this->v->array()->sizeof(2);

        $this->assertTrue($schema->isValid(['hexlet', 'code-basics']));
        $this->assertFalse($schema->isValid(['hexlet']));
        $this->assertFalse($schema->isValid([]));
    }

    /** @return array<string, array{array<string, mixed>, bool}> */
    public static function shapeProvider(): array
    {
        return [
            'всё заполнено' => [['name' => 'kolya', 'age' => 100], true],
            'необязательное поле пустое' => [['name' => 'maya', 'age' => null], true],
            'необязательного поля нет' => [['name' => 'maya'], true],
            'обязательное поле пустое' => [['name' => '', 'age' => null], false],
            'неверное значение' => [['name' => 'ada', 'age' => -5], false],
            'обязательного поля нет' => [['age' => 10], false],
        ];
    }

    /** @param array<string, mixed> $value */
    #[DataProvider('shapeProvider')]
    public function testArrayShape(array $value, bool $expected): void
    {
        $schema = $this->v->array()->shape([
            'name' => $this->v->string()->required(),
            'age' => $this->v->number()->positive(),
        ]);

        $this->assertSame($expected, $schema->isValid($value));
    }

    public function testNestedShape(): void
    {
        $schema = $this->v->array()->shape([
            'user' => $this->v->array()->required()->shape(['name' => $this->v->string()->required()]),
        ]);

        $this->assertTrue($schema->isValid(['user' => ['name' => 'kolya']]));
        $this->assertFalse($schema->isValid(['user' => ['name' => '']]));
        $this->assertFalse($schema->isValid([]));
    }

    public function testCustomStringValidator(): void
    {
        $this->v->addValidator('string', 'startWith', fn($value, $start) => str_starts_with($value, $start));
        $schema = $this->v->string()->test('startWith', 'H');

        $this->assertFalse($schema->isValid('exlet'));
        $this->assertTrue($schema->isValid('Hexlet'));
    }

    public function testCustomNumberValidator(): void
    {
        $this->v->addValidator('number', 'min', fn($value, $min) => $value >= $min);
        $schema = $this->v->number()->test('min', 5);

        $this->assertFalse($schema->isValid(4));
        $this->assertTrue($schema->isValid(6));
    }

    public function testValidatorAddedAfterSchemaCreation(): void
    {
        $schema = $this->v->string();
        $this->v->addValidator('string', 'isUpper', fn($value) => strtoupper($value) === $value);

        $this->assertTrue($schema->test('isUpper')->isValid('HEXLET'));
        $this->assertFalse($schema->isValid('hexlet'));
    }

    public function testCustomValidatorIsBoundToType(): void
    {
        $this->v->addValidator('string', 'startWith', fn($value, $start) => str_starts_with($value, $start));

        $this->expectException(InvalidArgumentException::class);
        $this->v->number()->test('startWith', 1);
    }

    public function testUnknownType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->v->addValidator('date', 'past', fn() => true);
    }
}
