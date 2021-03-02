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
    protected ?string $nameserver = null;

    public function __construct(
        protected ?Types $types = null,
        protected ?Factory $factory = null
    ) {
        $this->types ??= new Types();
        $this->factory ??= new Factory();
    }

    public function useNameserver(string $nameserver): self
    {
        $this->nameserver = $nameserver;

        return $this;
    }

    public function getNameserver(): ?string
    {
        return $this->nameserver;
    }

    public function getRecords(Domain|string $search, int|string|array $types = DNS_ALL): CollectionContract
    {
        $domain = $this->sanitizeDomain(strval($search));
        $types = $this->resolveTypes($types);

        $handler = $this->getHandler()->useNameserver($this->nameserver);

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
            fn (Handler $handler): bool => $handler->canHandle()
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

    protected function resolveTypes(int|string|array $type): array
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
