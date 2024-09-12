<?php declare(strict_types=1);

namespace Antwerpes\DataTransferObject\Casts;

use Antwerpes\DataTransferObject\CastsProperty;

readonly class ScalarCaster implements CastsProperty
{
    public function __construct(
        private array $types,
    ) {}

    public function unserialize(mixed $value): null|bool|float|int|string
    {
        if ($value === null) {
            return null;
        }

        foreach ($this->types as $type) {
            return match ($type) {
                'string' => (string) $value,
                'int' => (int) $value,
                'bool', 'false' => (bool) $value,
                'float' => (float) $value,
            };
        }

        return null;
    }

    public function serialize(mixed $value)
    {
        return $value;
    }
}
