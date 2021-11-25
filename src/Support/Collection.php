<?php

namespace Spatie\Dns\Support;

use Countable;

class Collection implements Countable
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
        foreach ($this->items as $item) {
            if ($callable($item)) {
                return $item;
            }
        }

        return null;
    }

    public function filter(callable $callable): Collection
    {
        $filteredItems = [];

        foreach ($this->items as $item) {
            if ($callable($item)) {
                $filteredItems[] = $item;
            }
        }

        return Collection::make($filteredItems);
    }

    public function __toString(): string
    {
        return implode(
            PHP_EOL,
            array_map('strval', $this->all())
        );
    }

    public function count(): int
    {
        return count($this->items);
    }
}
