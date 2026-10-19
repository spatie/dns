<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Throwable;

class DnsGetRecord extends Handler
{
    public function __invoke(string $domain, int $flag, string $type): array
    {
        if (in_array(mb_strtoupper($type), ['DS', 'DNSKEY'], true)) {
            throw CouldNotFetchDns::dnsGetRecordReturnedWithError(
                "The native PHP handler does not support fetching {$type} records. Please ensure 'dig' is installed."
            );
        }

        $records = false;
        $error = '';

        try {
            $records = dns_get_record($domain, $flag);
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }

        if ($records === false) {
            throw CouldNotFetchDns::dnsGetRecordReturnedWithError($error);
        }

        return $this->transform($type, $records);
    }

    public function canHandle(): bool
    {
        return function_exists('dns_get_record');
    }
}
