<?php

namespace Spatie\Dns\Testing;

use Spatie\Dns\Dns;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Support\Domain;
use Spatie\Dns\Support\Factory;
use Spatie\Dns\Support\Types;

class DnsFake extends Dns
{
    /** @var array|callable|null */
    protected $responder;

    public function __construct(?Types $types = null, ?Factory $factory = null, array|callable|null $responder = null)
    {
        parent::__construct($types, $factory);

        $this->responder = $responder ?? [];
    }

    public function getRecords(Domain|string $search, int|string|array $types = DNS_ALL): array
    {
        $domain = $this->sanitizeDomain(strval($search));
        $typeNames = array_values($this->resolveTypes($types));

        $response = $this->respond($domain, $typeNames);

        $records = [];

        foreach ($typeNames as $typeName) {
            $items = $response[$typeName] ?? ($response['*'] ?? []);

            foreach ((array) $items as $item) {
                if ($item instanceof Record) {
                    $records[] = $item;

                    continue;
                }

                if (is_string($item)) {
                    $records[] = $this->factory->parse($typeName, $item);

                    continue;
                }

                if (is_array($item)) {
                    $records[] = $this->factory->make($typeName, $item);

                    continue;
                }
            }
        }

        return $records;
    }

    public function when(string $domain, array|callable $response): self
    {
        if (! is_array($this->responder)) {
            $this->responder = [];
        }

        $this->responder[$domain] = $response;

        return $this;
    }

    public function respondWith(callable $callback): self
    {
        $this->responder = $callback;

        return $this;
    }

    /**
     * @param array<int,string> $typeNames
     * @return array<string, array<int, Record|array|string>>
     */
    protected function respond(string $domain, array $typeNames): array
    {
        if (is_callable($this->responder)) {
            $result = call_user_func($this->responder, $domain, $typeNames, $this->factory);

            return is_array($result) ? $result : [];
        }

        $map = $this->responder ?? [];

        $response = $map[$domain] ?? ($map['*'] ?? []);

        if (is_callable($response)) {
            $result = call_user_func($response, $domain, $typeNames, $this->factory);

            return is_array($result) ? $result : [];
        }

        return is_array($response) ? $response : [];
    }
}
