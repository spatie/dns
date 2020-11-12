<?php

namespace Spatie\Dns\Records;



/**
 * @method string ipv6()
 */
class AAAA extends Record
{
    protected string $ipv6;

    public static function parse(string $line): static
    {
        $attributes = static::lineToArray($line, 5);

        return static::make([
            'host' => trim($attributes[0], '.'),
            'ttl' => intval($attributes[1]),
            'class' => $attributes[2],
            'type' => mb_strtoupper($attributes[3]),
            'ipv6' => $attributes[4],
        ]);
    }

    public function __toString(): string
    {
        return sprintf(
            "%s.\t\t%d\t%s\t%s\t%s",
            $this->host,
            $this->ttl,
            $this->class,
            $this->type,
            $this->ipv6
        );
    }
}
