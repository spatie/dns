<?php

namespace Spatie\Dns\Records;

/**
 * @method string ipv6()
 */
class AAAA extends Record
{
    protected string $ipv6;

    public static function parse(string $line): ?self
    {
        $attributes = static::lineToArray($line, 5);

        if (count($attributes) < 5) {
            return null;
        }

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

    public function toArray()
    {
        return [
            'host' => $this->host,
            'ttl' => $this->ttl,
            'class' => $this->class,
            'type' => $this->type,
            'ipv6' => $this->ipv6,
        ];
    }
}
