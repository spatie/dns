<?php

namespace Spatie\Dns;

use Exception;
use Symfony\Component\Process\Process;
use Spatie\Dns\Exceptions\InvalidArgument;

class Dns
{
    protected $domain = '';

    protected $nameserver = '';

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

    public function __construct(string $domain, string $nameserver = '')
    {
        if (empty($domain)) {
            throw InvalidArgument::domainIsMissing();
        }

        $this->nameserver = $nameserver;

        $sanitized = $this->sanitizeDomainName($domain);

        $this->domain = $this->convertToPunycode($sanitized);
    }

    public function useNameserver(string $nameserver)
    {
        $this->nameserver = $nameserver;

        return $this;
    }

    public function getDomain(): string
    {
        return idn_to_utf8($this->domain, 0, INTL_IDNA_VARIANT_UTS46);
    }

    public function getNameserver(): string
    {
        return $this->nameserver;
    }

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

    public function convertToPunycode(string $domain): string
    {
        return idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46);
    }

    protected function sanitizeDomainName(string $domain): string
    {
        $domain = str_replace(['http://', 'https://'], '', $domain);

        $domain = strtok($domain, '/');

        return strtolower($domain);
    }

    protected function getRecordsOfType(string $type): string
    {
        $nameserverPart = $this->getSpecificNameserverPart();

        $command = 'CHARSET=ASCII dig +nocmd '.$nameserverPart.' '.escapeshellarg($this->domain)." {$type} +multiline +noall +answer";

        $process = new Process($command);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception('Dns records could not be fetched');
        }

        return $process->getOutput();
    }

    protected function getSpecificNameserverPart()
    {
        if ($this->nameserver === '') {
            return '';
        }

        return '@'.escapeshellarg($this->nameserver);
    }
}
