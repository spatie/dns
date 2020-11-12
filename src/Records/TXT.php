<?php

namespace Spatie\Dns\Records;



/**
 * @method string txt()
 */
class TXT extends Record
{
    protected string $txt;

    public static function parse(string $line): static
    {
        $attributes = static::lineToArray($line, 5);

        return static::make([
            'host' => trim($attributes[0], '.'),
            'ttl' => intval($attributes[1]),
            'class' => $attributes[2],
            'type' => strtoupper($attributes[3]),
            'txt' => trim($attributes[4], '"'),
        ]);
    }

    public function __toString(): string
    {
        return sprintf(
            "%s.\t\t%d\t%s\t%s\t\"%s\"",
            $this->host,
            $this->ttl,
            $this->class,
            $this->type,
            $this->txt
        );
    }
}