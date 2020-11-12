<?php

namespace Spatie\Dns\Records;



/**
 * @method int flags()
 * @method string tag()
 * @method string value()
 */
class CAA extends Record
{
    protected int $flags;
    protected string $tag;
    protected string $value;

    public static function parse(string $line): static
    {
        $attributes = static::lineToArray($line, 7);

        return static::make([
            'host' => trim($attributes[0], '.'),
            'ttl' => intval($attributes[1]),
            'class' => $attributes[2],
            'type' => strtoupper($attributes[3]),
            'flags' => intval($attributes[4]),
            'tag' => $attributes[5],
            'value' => trim($attributes[6], '"'),
        ]);
    }

    public function __toString(): string
    {
        return sprintf(
            "%s.\t\t%d\t%s\t%s\t%d\t%s\t\"%s\"",
            $this->host,
            $this->ttl,
            $this->class,
            $this->type,
            $this->flags,
            $this->tag,
            $this->value
        );
    }
}