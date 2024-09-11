<?php declare(strict_types=1);

namespace Antwerpes\DataTransferObject\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Map
{
    public function __construct(
        public ?string $from = null,
        public ?string $to = null,
    ) {}
}
