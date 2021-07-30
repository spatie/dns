<?php

namespace Spatie\Dns\Records;

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
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->ipv6}";
    }
}
