<?php

namespace Spatie\Dns\Records;

use Spatie\Dns\Support\Domain;

/**
 * @method string ip()
 */
class A extends Record
{
    protected string $ip;

    public static function parse(string $line): self
    {
        $attributes = static::lineToArray($line, 5);

        return static::make([
            'host' => $attributes[0],
            'ttl' => $attributes[1],
            'class' => $attributes[2],
            'type' => $attributes[3],
            'ip' => $attributes[4],
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
            $this->ip
        );
    }
}
