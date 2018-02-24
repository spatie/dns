<?php

namespace Spatie\Dns\DnsClient;

abstract class AbstractDnsClient implements DnsClientInterface
{
    protected $nameserver = '';

    protected $domain = '';

    public function getNameserver(): string
    {
        return $this->nameserver;
    }

    public function setNameserver(string $nameserver): DnsClientInterface
    {
        $this->nameserver = $nameserver;

        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): DnsClientInterface
    {
        $this->domain = $this->sanitizeDomainName($domain);

        return $this;
    }

    abstract public function getRecordsOfType(string $type): string;

    protected function sanitizeDomainName(string $domain): string
    {
        $domain = str_replace(['http://', 'https://'], '', $domain);

        $domain = strtok($domain, '/');

        return strtolower($domain);
    }
}
