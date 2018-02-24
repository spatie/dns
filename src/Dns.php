<?php

namespace Spatie\Dns;

use Spatie\Dns\DnsClient\Dig;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\DnsClient\DnsClientInterface;

class Dns
{
    protected $dnsClient;

    protected $recordTypes = [
        'A',
        'AAAA',
        'NS',
        'SOA',
        'MX',
        'TXT',
        'DNSKEY',
        'CAA',
    ];

    public function __construct(string $domain, string $nameserver = '', DnsClientInterface $dnsClient = null)
    {
        if (empty($domain)) {
            throw InvalidArgument::domainIsMissing();
        }

        $this->dnsClient = $dnsClient ?? new Dig();
        $this->dnsClient
            ->setDomain($domain)
            ->setNameserver($nameserver);
    }

    public function getRecords(...$types): string
    {
        $types = $this->determineTypes($types);

        $types = \count($types)
            ? $types
            : $this->recordTypes;

        $dnsRecords = array_map([$this->dnsClient, 'getRecordsOfType'], $types);

        return implode('', array_filter($dnsRecords));
    }

    protected function determineTypes(array $types): array
    {
        $types = \is_array($types[0] ?? null)
            ? $types[0]
            : $types;

        $types = array_map('strtoupper', $types);

        foreach ($types as $type) {
            if (! \in_array($type, $this->recordTypes, true)) {
                throw InvalidArgument::filterIsNotAValidRecordType($type, $this->recordTypes);
            }
        }

        return $types;
    }

    public function getDomain(): string
    {
        return $this->dnsClient->getDomain();
    }

    public function getNameserver(): string
    {
        return $this->dnsClient->getNameserver();
    }

    public function getDnsClient(): DnsClientInterface
    {
        return $this->dnsClient;
    }
}
