<?php

namespace Spatie\Dns\Contracts;

interface Collection
{
    public function all(): array;

    public function __toString(): string;
}
