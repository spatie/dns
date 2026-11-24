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

    /**
     * Fetches the records for several types. `$types` maps each flag to its type name, for
     * example `[DNS_A => 'A', DNS_MX => 'MX']`. The records come back grouped by type, in
     * the order of `$types`.
     *
     * This default calls `__invoke()` once per type. A handler that can fetch several types
     * at once can override this method. The override must return the same records, in the
     * same order, as this default. It must also throw where `__invoke()` would throw.
     *
     * @param  array<int, string>  $types
     * @return Record[]
     */
    public function getRecordsForTypes(string $domain, array $types): array
    {
        $records = [];

        foreach ($types as $flag => $type) {
            $records = array_merge($records, $this($domain, $flag, $type));
        }

        return $records;
    }

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
