<?php declare(strict_types=1);

namespace Antwerpes\DataTransferObject\Casts;

use Antwerpes\DataTransferObject\CastsProperty;
use Antwerpes\DataTransferObject\DataTransferObject;
use ArrayAccess;
use LogicException;
use Traversable;

readonly class ArrayCaster implements CastsProperty
{
    public function __construct(
        private array $types,
        private string $itemType,
    ) {}

    public function unserialize(mixed $value): array|ArrayAccess
    {
        foreach ($this->types as $type) {
            if ($type === 'array') {
                return $this->mapInto(destination: [], items: $value);
            }

            if (is_subclass_of($type, ArrayAccess::class)) {
                return $this->mapInto(destination: new $type, items: $value);
            }
        }

        throw new LogicException(
            'Caster [ArrayCaster] may only be used to cast arrays or objects that implement ArrayAccess.',
        );
    }

    public function serialize(mixed $value)
    {
        return $value;
    }

    private function mapInto(array|ArrayAccess $destination, mixed $items): array|ArrayAccess
    {
        if ($destination instanceof ArrayAccess && ! is_subclass_of($destination, Traversable::class)) {
            throw new LogicException(
                'Caster [ArrayCaster] may only be used to cast ArrayAccess objects that are traversable.',
            );
        }

        foreach ($items as $key => $item) {
            $destination[$key] = $this->castItem($item);
        }

        return $destination;
    }

    private function castItem(mixed $data)
    {
        if ($data instanceof $this->itemType) {
            return $data;
        }

        if (is_array($data)) {
            if (is_subclass_of($this->itemType, DataTransferObject::class)) {
                return $this->itemType::decode($data);
            }

            return new $this->itemType(...$data);
        }

        throw new LogicException(
            "Caster [ArrayCaster] each item must be an array or an instance of the specified item type [{$this->itemType}].",
        );
    }
}
