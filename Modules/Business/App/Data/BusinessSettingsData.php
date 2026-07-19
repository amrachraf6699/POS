<?php

namespace Modules\Business\App\Data;

final class BusinessSettingsData
{
    public function __construct(public readonly array $attributes) {}

    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }
}
