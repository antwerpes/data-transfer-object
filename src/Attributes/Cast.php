<?php declare(strict_types=1);

namespace Antwerpes\DataTransferObject\Attributes;

use Antwerpes\DataTransferObject\CastsProperty;
use Attribute;
use LogicException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cast
{
    public array $args;

    public function __construct(
        public string $casterClass,
        mixed ...$args,
    ) {
        if (! is_subclass_of($this->casterClass, CastsProperty::class)) {
            throw new LogicException("Caster [{$this->casterClass}] must implement the CastsProperty interface.");
        }

        $this->args = $args;
    }
}
