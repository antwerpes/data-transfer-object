# Data Transfer Object

[![Latest Version on Packagist](https://img.shields.io/packagist/v/antwerpes/data-transfer-object.svg?style=flat-square)](https://packagist.org/packages/antwerpes/data-transfer-object)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/antwerpes/data-transfer-object/lint.yml?branch=master)](https://github.com/antwerpes/data-transfer-object/actions?query=workflow%3Alint+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/antwerpes/data-transfer-object.svg?style=flat-square)](https://packagist.org/packages/antwerpes/data-transfer-object)

Simple library for encoding and decoding JSON structures into PHP objects, e.g. to work with API responses
in a strongly typed way.

## Installation

You can install the package via composer:

```bash
composer require antwerpes/data-transfer-object
```

## Usage

Define a class that extends `Antwerpes\DataTransferObject\DataTransferObject` and define the structure of the object:

```php
use Antwerpes\DataTransferObject\Attributes\Cast;
use Antwerpes\DataTransferObject\Attributes\Map;
use Antwerpes\DataTransferObject\Casts\ArrayCaster;
use Antwerpes\DataTransferObject\DataTransferObject;

class User extends DataTransferObject
{
    public function __construct(
        public string $name;
        #[Cast(CustomDateCaster::class)]
        public DateTimeInterface $birthday;
        #[Map(from: 'address.city')]
        public string $city;
        #[Cast(ArrayCaster::class, itemType: Interest:class)]
        public array $interests;
    ) {}
}
```

Then you can use the class to decode JSON strings into PHP objects:

```php
$json = '{
    "name": "John Doe",
    "birthday": "1990-01-01",
    "address": {
        "city": "New York"
    },
    "interests": [
        {
            "name": "Music"
        },
        {
            "name": "Programming"
        }
    ]
}';
$user = User::decode(json_decode($json, true));
$encoded = $user->encode();
```

### Custom Casters

You can define custom casters by implementing the `Antwerpes\DataTransferObject\CastsProperty` interface:

```php
use Antwerpes\DataTransferObject\CastsProperty;

class CustomDateCaster implements CastsProperty
{
    public function unserialize(mixed $value): DateTimeInterface
    {
        return new DateTime($value);
    }
    
    public function serialize(mixed $value): string
    {
        return $value->format('Y-m-d');
    }
}
```

### Mapping

You can map nested properties to a flat structure using the `Map` attribute:

```php
use Antwerpes\DataTransferObject\Attributes\Map;

class User extends DataTransferObject
{
    #[Map(from: 'address.city', to: 'address.city')]
    public string $city;
}
```

### Validation

Validation is out of scope for this package, use JSON schemas or other libraries like `symfony/validator` 
to validate the object.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Leave an issue on GitHub, or create a Pull Request.

## Credits

- [Elisha Witte](https://github.com/chiiya)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
