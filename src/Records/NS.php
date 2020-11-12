<?php

namespace Spatie\Dns\Records;

/**
 * @method string target()
 */
class NS extends Record
{
    protected string $target;

    public static function parse(string $line): self
    {
        $attributes = static::lineToArray($line, 5);

        return static::make([
            'host' => trim($attributes[0], '.'),
            'ttl' => intval($attributes[1]),
            'class' => $attributes[2],
            'type' => mb_strtoupper($attributes[3]),
            'target' => trim($attributes[4], '.'),
        ]);
    }

    public function __toString(): string
    {
        return sprintf(
            "%s.\t\t%d\t%s\t%s\t%s.",
            $this->host,
            $this->ttl,
            $this->class,
            $this->type,
            $this->target
        );
    }
}
