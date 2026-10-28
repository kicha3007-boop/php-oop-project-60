# Валидатор данных (PHP)

[![hexlet-check](https://github.com/kicha3007-boop/php-oop-project-60/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/kicha3007-boop/php-oop-project-60/actions)
[![CI](https://github.com/kicha3007-boop/php-oop-project-60/actions/workflows/ci.yml/badge.svg)](https://github.com/kicha3007-boop/php-oop-project-60/actions/workflows/ci.yml)

Библиотека для проверки данных с fluent-интерфейсом по мотивам [yup](https://github.com/jquense/yup):
строки, числа, массивы со вложенными схемами и собственные правила.

## Установка

Нужны PHP 8.3+ и Composer.

```bash
git clone https://github.com/kicha3007-boop/php-oop-project-60.git
cd php-oop-project-60
make install   # зависимости
make test      # тесты (PHPUnit)
make lint      # PHP_CodeSniffer (PSR-12) и PHPStan
```

Без локального PHP — в контейнере:

```bash
docker run --rm -v "$PWD:/app" -w /app composer:2 sh -c "composer install && make lint test"
```

## Использование

```php
use Hexlet\Validator\Validator;

$v = new Validator();

// строки: required — непустая строка, minLength, contains
$name = $v->string()->required()->minLength(3);
$name->isValid('hexlet'); // true
$name->isValid('');       // false

// числа: required — любое число, включая 0; positive; range — границы включительно
$age = $v->number()->required()->positive()->range(1, 120);
$age->isValid(30); // true
$age->isValid(-1); // false

// массивы: required — значение-массив; sizeof — точная длина; shape — схемы для ключей
$user = $v->array()->shape([
    'name' => $v->string()->required(),
    'age' => $v->number()->positive(),
]);
$user->isValid(['name' => 'kolya', 'age' => 100]); // true
$user->isValid(['name' => '', 'age' => null]);     // false

// собственные правила: addValidator(тип, имя, fn), вызов через test(имя, ...аргументы)
$v->addValidator('string', 'startWith', fn($value, $start) => str_starts_with($value, $start));
$v->string()->test('startWith', 'H')->isValid('Hexlet'); // true
```

Без `required()` пустое значение (`null`, у строки ещё `''`) проходит проверку.
Повторный вызов правила заменяет прежнее.

## Устройство

- `src/Validator.php` — фабрика схем и реестр пользовательских правил по паре «тип и имя»;
- `src/CustomValidators.php` — пользовательские правила одного типа;
- `src/Schemas/BaseSchema.php` — общий базовый класс: правила хранятся по имени, `required`, `test`, `isValid`;
- `src/Schemas/*Schema.php` — схемы типов, каждая добавляет только свои правила.
