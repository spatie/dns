<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Support\Factory;

abstract class Handler
{
    protected $nameserver = null;
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function useNameserver(?string $nameserver): self
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
        return array_filter(array_map(
            function ($record) use ($type): ?Record {
                try {
                    return is_string($record)
                        ? $this->factory->parse($type, $record)
                        : $this->factory->make($type, $record);
                } catch (InvalidArgument $e) {
                    return null;
                }
            },
            $records
        ));
    }
}
