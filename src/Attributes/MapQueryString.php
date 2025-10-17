<?php

namespace LaravelDtoMapper\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapQueryString
{
    public function __construct(
        public bool $validate = true,
        public bool $stopOnFirstFailure = false
    ) {
    }
}
