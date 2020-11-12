<?php

namespace Spatie\Dns\Records;

use Spatie\Dns\Support\Domain;

/**
 * @method string ipv6()
 */
class AAAA extends Record
{
    protected string $ipv6;

    public static function parse(string $line): self
    {
        $attributes = static::lineToArray($line, 5);

        return static::make([
            'host' => $attributes[0],
            'ttl' => $attributes[1],
            'class' => $attributes[2],
            'type' => $attributes[3],
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
