<?php

namespace Spatie\Dns;

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

    /** @var array<int, Handler> */
    protected ?array $customHandlers = null;

    public static function query(?Types $types = null, ?Factory $factory = null): self
    {
        return new static($types, $factory);
    }

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

    public function getRecords(
        Domain | string $search,
        int | string | array $types = DNS_ALL
    ): array {
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

        return $records;
    }

    /**
     * @param array<int, Handler> $customHandlers
     */
    public function useHandlers(array $customHandlers): self
    {
        $this->customHandlers = $customHandlers;

        return $this;
    }

    protected function getHandler(): Handler
    {
        $handler = Collection::make($this->customHandlers ?? $this->defaultHandlers())
            ->first(fn (Handler $handler) => $handler->canHandle());

        if (! $handler) {
            throw CouldNotFetchDns::noHandlerFound();
        }

        return $handler;
    }

    /**
     * @return array<int, Handler>
     */
    protected function defaultHandlers(): array
    {
        return [
            new Dig($this->factory),
            new DnsGetRecord($this->factory),
        ];
    }

    protected function sanitizeDomain(string $input): string
    {
        return strval(new Domain($input));
    }

    protected function resolveTypes(int | string | array $type): array
    {
        $flags = match (true) {
            is_string($type) && $type === '*' => DNS_ALL,
            is_string($type) && in_array(mb_strtoupper($type), Types::getTypes()) => $this->types->toFlags([$type]),
            is_int($type) => $type,
            is_array($type) => $this->types->toFlags($type),
            default => throw InvalidArgument::invalidRecordType(),
        };

        return $this->types->toNames($flags);
    }
}
