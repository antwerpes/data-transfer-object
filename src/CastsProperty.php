<?php declare(strict_types=1);

namespace Antwerpes\DataTransferObject;

interface CastsProperty
{
    public function unserialize(mixed $value);

    public function serialize(mixed $value);
}
