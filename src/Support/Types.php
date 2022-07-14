<?php

namespace Spatie\Dns\Support;

class Types
{
    public static function getTypes()
    {
        $dnsTypes = [
            DNS_A => 'A',
            DNS_AAAA => 'AAAA',
            DNS_CNAME => 'CNAME',
            DNS_NS => 'NS',
            DNS_PTR => 'PTR',
            DNS_SOA => 'SOA',
            DNS_MX => 'MX',
            DNS_SRV => 'SRV',
            DNS_TXT => 'TXT',
            // DNS_NAPTR => 'NAPTR',
        ];
        //@see https://bugs.php.net/bug.php?id=75909
        if (defined('DNS_CAA')) {
            $dnsTypes[DNS_CAA] = 'CAA';
        }

        return $dnsTypes;
    }

    public function toNames(int $flags): array
    {
        $types = [];

        foreach (static::getTypes() as $flag => $type) {
            if ($flags & $flag) {
                $types[$flag] = $type;
            }
        }

        return $types;
    }

    public function toFlags(array $types): int
    {
        $flags = 0;

        foreach ($types as $type) {
            $flag = array_search(mb_strtoupper($type), static::getTypes());

            if ($flag !== false) {
                $flags = $flags | $flag;
            }
        }

        return $flags;
    }
}
