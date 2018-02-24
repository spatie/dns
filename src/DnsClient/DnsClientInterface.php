<?php

namespace Spatie\Dns\DnsClient;

interface DnsClientInterface
{
    public function getRecordsOfType(string $type): string;

    public function getNameserver(): string;

    public function setNameserver(string $nameserver): self;

    public function getDomain(): string;

    public function setDomain(string $domain): self;
}
