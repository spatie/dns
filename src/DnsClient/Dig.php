<?php

namespace Spatie\Dns\DnsClient;

use Exception;
use Symfony\Component\Process\Process;

class Dig extends AbstractDnsClient
{
    public function getRecordsOfType(string $type): string
    {
        $nameserverPart = $this->getSpecificNameserverPart();

        $command = 'dig +nocmd '.$nameserverPart.' '.escapeshellarg($this->domain)." {$type} +multiline +noall +answer";

        $process = new Process($command);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception('Dns records could not be fetched');
        }

        return $process->getOutput();
    }

    protected function getSpecificNameserverPart(): string
    {
        if ($this->nameserver === '') {
            return '';
        }

        return '@'.escapeshellarg($this->nameserver);
    }
}
