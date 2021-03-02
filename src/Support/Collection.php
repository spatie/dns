<?php

namespace Spatie\Dns\Support;

use Spatie\Dns\Contracts\Collection as CollectionContract;

class Collection implements CollectionContract
{
    /** @var \Spatie\Dns\Records\Record[] */
    protected array $records;

    /**
     * @param \Spatie\Dns\Records\Record[] $records
     */
    public function __construct(array $records)
    {
        $this->records = $records;
    }

    public function all(): array
    {
        return $this->records;
    }

    public function __toString(): string
    {
        return implode(
            PHP_EOL,
            array_map('strval', $this->all())
        );
    }
}
