<?php

namespace Spatie\Dns\Support;

class Types
{
    public const TYPES = [
        DNS_A => 'A',
        DNS_AAAA => 'AAAA',
        DNS_CNAME => 'CNAME',
        DNS_NS => 'NS',
        DNS_SOA => 'SOA',
        DNS_MX => 'MX',
        DNS_SRV => 'SRV',
        DNS_TXT => 'TXT',
        DNS_CAA => 'CAA',
        // DNS_NAPTR => 'NAPTR',
    ];

    public function toNames(int $flags): array
    {
        $types = [];

        foreach (self::TYPES as $flag => $type) {
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
            $flag = array_search(mb_strtoupper($type), self::TYPES);

            if ($flag !== false) {
                $flags = $flags | $flag;
            }
        }

        return $flags;
    }
}
