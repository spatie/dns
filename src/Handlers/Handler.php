<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Records\Record;
use Spatie\Dns\Support\Factory;

abstract class Handler
{
    protected ?string $nameserver = null;

    protected Factory $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function setNameserver(?string $nameserver): self
    {
        $this->nameserver = $nameserver;

        return $this;
    }

    /**
     * @param string $domain
     * @param int $flag
     * @param string $type
     * @return \Spatie\Dns\Records\Record[]
     */
    abstract public function __invoke(string $domain, int $flag, string $type): array;

    abstract public function canHandle(): bool;

    protected function transform(string $type, array $records): array
    {
        return array_map(
            function ($record) use ($type): Record {
                if (is_string($record)) {
                    return $this->factory->parse($type, $record);
                }

                return $this->factory->make($type, $record);
            },
            $records
        );
    }
}
