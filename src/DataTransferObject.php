<?php declare(strict_types=1);

namespace Antwerpes\DataTransferObject;

use Antwerpes\DataTransferObject\Reflection\DataTransferObjectProperty;
use ReflectionClass;
use ReflectionProperty;

class DataTransferObject
{
    public static function decode(array $data): static
    {
        $class = new ReflectionClass(static::class);
        $args = [];
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $reflectionProperty) {
            if ($reflectionProperty->isStatic()) {
                continue;
            }

            $property = new DataTransferObjectProperty($reflectionProperty);
            $value = Arr::get($data, $property->from, $property->getDefaultValue());

            if ($property->caster instanceof CastsProperty) {
                $value = $property->caster->unserialize($value);
            }

            $args[$property->getPropertyName()] = $value;
        }

        return new static(...$args);
    }

    public function encode(): array
    {
        $data = [];
        $properties = $this->getProperties();

        foreach ($properties as $reflectionProperty) {
            $property = new DataTransferObjectProperty($reflectionProperty);
            $value = $property->getValue($this);

            if ($property->caster instanceof CastsProperty) {
                $value = $property->caster->serialize($value);
            }

            Arr::set($data, $property->to, match (true) {
                $value instanceof self => $value->encode(),
                is_array($value) => $this->processArray($value),
                default => $value,
            });
        }

        return $data;
    }

    protected function processArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->encode();

                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $array[$key] = $this->processArray($value);
        }

        return $array;
    }

    protected function getProperties(): array
    {
        $class = new ReflectionClass(static::class);

        return array_values(array_filter(
            $class->getProperties(ReflectionProperty::IS_PUBLIC),
            fn (ReflectionProperty $property) => ! $property->isStatic(),
        ));
    }
}
