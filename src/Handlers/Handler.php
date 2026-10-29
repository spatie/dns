<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Support\Factory;

abstract class Handler
{
    protected ?string $nameserver = null;

    protected ?int $timeout = null;

    protected ?int $retries = null;

    protected bool $throwOnErrorResponse = false;

    public function __construct(protected Factory $factory) {}

    public function useNameserver(?string $nameserver): self
    {
        $this->nameserver = $nameserver;

        return $this;
    }

    public function setTimeout(?int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setRetries(?int $retries): self
    {
        $this->retries = $retries;

        return $this;
    }

    public function throwOnErrorResponse(bool $throwOnErrorResponse): self
    {
        $this->throwOnErrorResponse = $throwOnErrorResponse;

        return $this;
    }

    /**
     * @return Record[]
     */
    abstract public function __invoke(string $domain, int $flag, string $type): array;

    abstract public function canHandle(): bool;

    protected function transform(string $type, array $records): array
    {
        return array_filter(array_map(
            function (string|array $record) use ($type): ?Record {
                try {
                    return is_string($record)
                        ? $this->factory->parse($type, $record)
                        : $this->factory->make($type, $record);
                } catch (InvalidArgument) {
                    return null;
                }
            },
            $records
        ));
    }
}
