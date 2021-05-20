<?php

namespace Spatie\Dns\Support;

class Collection
{
    protected array $items;

    public static function make(array $items): self
    {
        return new static($items);
    }

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function first(callable $callable): mixed
    {
        foreach ($this->items as $record) {
            if ($callable($record)) {
                return $record;
            }
        }

        return null;
    }

    public function __toString(): string
    {
        return implode(
            PHP_EOL,
            array_map('strval', $this->all())
        );
    }
}
