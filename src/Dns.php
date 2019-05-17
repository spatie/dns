<?php

namespace Spatie\Dns;

use Symfony\Component\Process\Process;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Exceptions\CouldNotFetchDns;

class Dns
{
    protected $domain = '';

    protected $nameserver = '';

    protected $recordTypes = [
        'A',
        'AAAA',
        'CNAME',
        'NS',
        'SOA',
        'MX',
        'SRV',
        'TXT',
        'DNSKEY',
        'CAA',
        'NAPTR',
    ];

    public function __construct(string $domain, string $nameserver = '')
    {
        if (empty($domain)) {
            throw InvalidArgument::domainIsMissing();
        }

        $this->nameserver = $nameserver;

        $this->domain = $this->sanitizeDomainName($domain);
    }

    public function useNameserver(string $nameserver)
    {
        $this->nameserver = $nameserver;

        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getNameserver(): string
    {
        return $this->nameserver;
    }

    /**
     * @throws CouldNotFetchDns
     */
    public function getRecords(...$types): string
    {
        $types = $this->determineTypes($types);

        $types = count($types)
            ? $types
            : $this->recordTypes;

        $dnsRecords = array_map([$this, 'getRecordsOfType'], $types);

        return implode('', array_filter($dnsRecords));
    }

    protected function determineTypes(array $types): array
    {
        $types = is_array($types[0] ?? null)
            ? $types[0]
            : $types;

        $types = array_map('strtoupper', $types);

        foreach ($types as $type) {
            if (! in_array($type, $this->recordTypes)) {
                throw InvalidArgument::filterIsNotAValidRecordType($type, $this->recordTypes);
            }
        }

        return $types;
    }

    protected function sanitizeDomainName(string $domain): string
    {
        $domain = str_replace(['http://', 'https://'], '', $domain);

        $domain = strtok($domain, '/');

        return strtolower($domain);
    }

    /**
     * @throws CouldNotFetchDns
     */
    protected function getRecordsOfType(string $type): string
    {
        $nameserverPart = $this->getSpecificNameserverPart();

        $command = array_filter([
            'dig',
            '+nocmd',
            $nameserverPart,
            $this->domain,
            $type,
            '+multiline',
            '+noall',
            '+answer',
        ]);

        $process = new Process($command);

        $process->run();

        if (! $process->isSuccessful()) {
            throw CouldNotFetchDns::digReturnedWithError(trim($process->getErrorOutput()));
        }

        return $process->getOutput();
    }

    protected function getSpecificNameserverPart(): ?string
    {
        if ($this->nameserver === '') {
            return null;
        }

        return '@'.$this->nameserver;
    }
}
