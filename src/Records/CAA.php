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

    public static function parse(string $line): self
    {
        $attributes = static::lineToArray($line, 7);

        return static::make([
            'host' => $attributes[0],
            'ttl' => $attributes[1],
            'class' => $attributes[2],
            'type' => $attributes[3],
            'flags' => $attributes[4],
            'tag' => $attributes[5],
            'value' => $attributes[6],
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

    protected function castFlags(string $value): int
    {
        return $this->prepareInt($value);
    }

    protected function castValue(string $value): string
    {
        return $this->prepareText($value);
    }
}
