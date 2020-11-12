<?php

namespace Spatie\Dns\Records;



/**
 * @method int pri()
 * @method string target()
 */
class MX extends Record
{
    protected int $pri;
    protected string $target;

    public static function parse(string $line): static
    {
        $attributes = static::lineToArray($line, 6);

        return static::make([
            'host' => trim($attributes[0], '.'),
            'ttl' => intval($attributes[1]),
            'class' => $attributes[2],
            'type' => strtoupper($attributes[3]),
            'pri' => intval($attributes[4]),
            'target' => trim($attributes[5], '.'),
        ]);
    }

    public function __toString(): string
    {
        return sprintf(
            "%s.\t\t%d\t%s\t%s\t%d\t%s.",
            $this->host,
            $this->ttl,
            $this->class,
            $this->type,
            $this->pri,
            $this->target
        );
    }
}