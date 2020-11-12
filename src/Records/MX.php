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

    public static function parse(string $line): self
    {
        $attributes = static::lineToArray($line, 6);

        return static::make([
            'host' => $attributes[0],
            'ttl' => $attributes[1],
            'class' => $attributes[2],
            'type' => $attributes[3],
            'pri' => $attributes[4],
            'target' => $attributes[5],
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

    protected function castPri($value): string
    {
        return $this->prepareInt($value);
    }

    protected function castTarget(string $value): string
    {
        return $this->prepareDomain($value);
    }
}
