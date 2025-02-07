<?php declare(strict_types=1);

namespace Antwerpes\DataTransferObject\Reflection;

use Antwerpes\DataTransferObject\Attributes\Cast;
use Antwerpes\DataTransferObject\Attributes\Map;
use Antwerpes\DataTransferObject\Casts\DTOCaster;
use Antwerpes\DataTransferObject\Casts\ScalarCaster;
use Antwerpes\DataTransferObject\CastsProperty;
use Antwerpes\DataTransferObject\DataTransferObject;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

class DataTransferObjectProperty
{
    public string $from;
    public string $to;
    public ?CastsProperty $caster;

    public function __construct(
        private readonly ReflectionProperty $property,
    ) {
        $this->from = $this->resolveFrom();
        $this->to = $this->resolveTo();
        $this->caster = $this->resolveCaster();
    }

    public function getPropertyName(): string
    {
        return $this->property->getName();
    }

    public function getDefaultValue(): mixed
    {
        $class = $this->property->getDeclaringClass();

        do {
            if ($class->getConstructor() instanceof ReflectionMethod) {
                foreach ($class->getConstructor()->getParameters() as $parameter) {
                    if (($parameter->getName() === $this->property->getName()) && $parameter->isDefaultValueAvailable()) {
                        return $parameter->getDefaultValue();
                    }
                }
            }
        } while ($class = $class->getParentClass());

        return $this->property->getDefaultValue();
    }

    public function getValue(object $object): mixed
    {
        return $this->property->getValue($object);
    }

    private function resolveFrom(): string
    {
        $mapAttribute = $this->property->getAttributes(Map::class);

        if (! count($mapAttribute)) {
            return $this->property->getName();
        }

        return $mapAttribute[0]->newInstance()->from ?: $this->property->getName();
    }

    private function resolveTo(): string
    {
        $mapAttribute = $this->property->getAttributes(Map::class);

        if (! count($mapAttribute)) {
            return $this->property->getName();
        }

        return $mapAttribute[0]->newInstance()->to ?: $this->property->getName();
    }

    private function resolveCaster(): ?CastsProperty
    {
        $castWithAttribute = $this->property->getAttributes(Cast::class);
        $types = $this->extractTypes();

        if (count($castWithAttribute)) {
            $caster = $castWithAttribute[0]->newInstance();

            return new $caster->casterClass($types, ...$caster->args);
        }

        foreach ($types as $type) {
            if (is_subclass_of($type, DataTransferObject::class)) {
                return new DTOCaster($types);
            }
        }

        if (array_intersect($types, ['string', 'int', 'bool', 'false', 'float'])) {
            return new ScalarCaster($types);
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function extractTypes(): array
    {
        $type = $this->property->getType();

        if (! $type) {
            return [];
        }

        return match ($type::class) {
            ReflectionNamedType::class => [$type->getName()],
            ReflectionUnionType::class => array_map(fn (ReflectionNamedType $t) => $t->getName(), $type->getTypes()),
        };
    }
}
