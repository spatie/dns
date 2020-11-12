<?php

namespace Spatie\Dns;

use Spatie\Dns\Contracts\Collection as CollectionContract;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Handlers\Dig;
use Spatie\Dns\Handlers\DnsGetRecord;
use Spatie\Dns\Handlers\Handler;
use Spatie\Dns\Support\Collection;
use Spatie\Dns\Support\Domain;
use Spatie\Dns\Support\Factory;
use Spatie\Dns\Support\Types;

class Dns
{
    protected ?Types $types;

    protected ?Factory $factory;

    protected ?string $nameserver = null;

    public function __construct(
        ?Types $types = null,
        ?Factory $factory = null
    )
    {
        $this->types = $types ?? new Types();

        $this->factory = $factory ?? new Factory();
    }

    public function useNameserver(string $nameserver): static
    {
        $this->nameserver = $nameserver;

        return $this;
    }

    public function getNameserver(): ?string
    {
        return $this->nameserver;
    }

    /**
     * @param \Spatie\Dns\Support\Domain|string $search
     * @param int|string|string[] $types
     *
     * @return \Spatie\Dns\Contracts\Collection
     */
    public function getRecords($search, $types = DNS_ALL): CollectionContract
    {
        $domain = $this->sanitizeDomain(strval($search));
        $types = $this->resolveTypes($types);

        $handler = $this->getHandler()->setNameserver($this->nameserver);

        $records = [];

        foreach ($types as $flag => $type) {
            $records = array_merge(
                $records,
                $handler($domain, $flag, $type)
            );
        }

        return $this->collect($records);
    }

    protected function collect(array $records): CollectionContract
    {
        return new Collection($records);
    }

    protected function getHandler(): Handler
    {
        $handlers = array_filter(
            [
                new Dig($this->factory),
                new DnsGetRecord($this->factory),
            ],
            fn(Handler $handler): bool => $handler->canHandle()
        );

        if (empty($handlers)) {
            throw CouldNotFetchDns::noHandlerFound();
        }

        return array_shift($handlers);
    }

    protected function sanitizeDomain(string $input): string
    {
        return strval(new Domain($input));
    }

    /**
     * @param int|string|string[] $type
     *
     * @return string[]
     * @example $this->resolveTypes("A")
     * @example $this->resolveTypes(["A", "MX"])
     * @example $this->resolveTypes(DNS_A | DNS_MX)
     *
     * @see Types::TYPES
     *
     * @example $this->resolveTypes("*")
     */
    protected function resolveTypes($type): array
    {
        if (is_string($type) && $type === '*') {
            return $this->types->toNames(DNS_ALL);
        }

        if (is_string($type) && in_array(mb_strtoupper($type), Types::TYPES)) {
            return $this->types->toNames($this->types->toFlags([$type]));
        }

        if (is_int($type)) {
            return $this->types->toNames($type);
        }

        if (is_array($type)) {
            return $this->types->toNames($this->types->toFlags($type));
        }

        throw InvalidArgument::invalidRecordType();
    }
}
