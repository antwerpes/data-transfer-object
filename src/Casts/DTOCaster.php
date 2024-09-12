<?php declare(strict_types=1);

namespace Antwerpes\DataTransferObject\Casts;

use Antwerpes\DataTransferObject\CastsProperty;
use Antwerpes\DataTransferObject\DataTransferObject;

readonly class DTOCaster implements CastsProperty
{
    public function __construct(
        private array $types,
    ) {}

    public function unserialize(mixed $value): ?DataTransferObject
    {
        if ($value === null) {
            return null;
        }

        foreach ($this->types as $type) {
            if ($value instanceof $type) {
                return $value;
            }
        }

        return $this->types[0]::decode($value);
    }

    public function serialize(mixed $value)
    {
        return $value->encode();
    }
}
