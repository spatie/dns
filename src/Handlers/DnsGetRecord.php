<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Records\Record;
use Spatie\Dns\Support\Factory;
use Spatie\Dns\Support\Types;

class DnsGetRecord extends Handler
{
    public function __invoke(string $domain, int $flag, string $type): array
    {
        $records = dns_get_record($domain, $flag);

        return $this->transform($type, $records);
    }

    public function canHandle(): bool
    {
        return function_exists('dns_get_record');
    }
}
