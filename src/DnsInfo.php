<?php

namespace Spatie\DnsInfo;

use Exception;
use Spatie\DnsInfo\Helpers\Helper;
use Symfony\Component\Process\Process;

class DnsInfo
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
     * Create a new DnsInfo Instance.
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
     * Get A type records.
     *
     * @return string
     */
    public function getARecords(): string
    {
        return $this->retrieveDnsRecords($this->getRecordType(0));
    }

    /**
     * Get AAAA type records.
     *
     * @return string
     */
    public function getAAAARecords(): string
    {
        return $this->retrieveDnsRecords($this->getRecordType(1));
    }

    /**
     * Get NS type records.
     *
     * @return string
     */
    public function getNSRecords(): string
    {
        return $this->retrieveDnsRecords($this->getRecordType(2));
    }

    /**
     * Get SOA type records.
     *
     * @return string
     */
    public function getSOARecords(): string
    {
        return $this->retrieveDnsRecords($this->getRecordType(3));
    }

    /**
     * Get MX type records.
     *
     * @return string
     */
    public function getMXRecords(): string
    {
        return $this->retrieveDnsRecords($this->getRecordType(4));
    }

    /**
     * Get TXT type records.
     *
     * @return string
     */
    public function getTXTRecords(): string
    {
        return $this->retrieveDnsRecords($this->getRecordType(5));
    }

    /**
     * Get DNSKEY type records.
     *
     * @return string
     */
    public function getDNSKEYRecords(): string
    {
        return $this->retrieveDnsRecords($this->getRecordType(6));
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
     * Get the record type.
     *
     * @param int
     * @return array
     */
    protected function getRecordType(int $index): array
    {
        return array ($this->recordTypes[$index]);
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
