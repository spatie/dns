<?php

namespace Spatie\Dns;

use Exception;
use Spatie\Dns\Helpers\Helper;
use Symfony\Component\Process\Process;

class Dns
{
    /**
     * The domain name to get DNS records for.
     */
    protected $domain;

    /**
     * All DNS record types.
     */
    protected $recordTypes = ['A', 'AAAA', 'NS', 'SOA', 'MX', 'TXT', 'DNSKEY'];

    /**
     * Create a new Dns Instance.
     */
    public function __construct(string $domain)
    {
        $this->domain = $this->sanitizeDomainName($domain);
    }

    /**
     * Get the Domain name.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get All DNS records.
     *
     * @return string
     */
    public function getAllRecords(): string
    {
        return $this->retrieveDnsRecords($this->recordTypes);
    }

    /**
     * Get DNS records for given record types.
     *
     * @return string
     */
    public function getRecords($recordTypes): string
    {
        $recordTypes = is_array($recordTypes) ? array_map('strtoupper', $recordTypes) : array (strtoupper($recordTypes));

        if (empty(array_intersect($recordTypes, $this->recordTypes))) {
            throw new Exception("No such record type");
        }

        return $this->retrieveDnsRecords($recordTypes);
    }

    /**
     * Get A type records.
     *
     * @return string
     */
    public function getARecords(): string
    {
        return $this->getRecords('A');
    }

    /**
     * Get AAAA type records.
     *
     * @return string
     */
    public function getAaaaRecords(): string
    {
        return $this->getRecords('AAAA');
    }

    /**
     * Get NS type records.
     *
     * @return string
     */
    public function getNsRecords(): string
    {
        return $this->getRecords('NS');
    }

    /**
     * Get SOA type records.
     *
     * @return string
     */
    public function getSoaRecords(): string
    {
        return $this->getRecords('SOA');
    }

    /**
     * Get MX type records.
     *
     * @return string
     */
    public function getMxRecords(): string
    {
        return $this->getRecords('MX');
    }

    /**
     * Get TXT type records.
     *
     * @return string
     */
    public function getTxtRecords(): string
    {
        return $this->getRecords('TXT');
    }

    /**
     * Get DNSKEY type records.
     *
     * @return string
     */
    public function getDnsKeyRecords(): string
    {
        return $this->getRecords('DNSKEY');
    }

    /**
     * Sanatize the given domain name.
     *
     * @param string
     * @return string
     */
    protected function sanitizeDomainName(string $domain): string
    {
        if (empty($domain)) {
            throw new Exception('No domain name provided.');
        }

        $domain = str_replace(['http://', 'https://'], '', $domain);

        $domain = Helper::str_before($domain, '/');

        return strtolower($domain);
    }

    /**
     * Retrieve the asked DNS records.
     *
     * @param array
     * @return string
     */
    protected function retrieveDnsRecords(array $recordTypes): string
    {
        $records = '';

        foreach ($recordTypes as $recordType) {

            if (! in_array($recordType, $this->recordTypes)) {
                throw new Exception("No such record type");
            }

            $command = 'dig +nocmd ' . escapeshellarg($this->domain) . " {$recordType} +multiline +noall +answer";

            $process = new Process($command);

            $process->run();

            if (! $process->isSuccessful()) {
                throw new Exception('Dns records could not be fetched');
            }

            $records .= $process->getOutput();
        }

        return $records;
    }
}
