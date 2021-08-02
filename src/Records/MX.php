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

    public static function parse(string $line): ?self
    {
        $attributes = static::lineToArray($line, 6);

        if (count($attributes) < 6) {
            return null;
        }

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
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->pri}\t{$this->target}.";
    }

    protected function castPri($value): string
    {
        return $this->prepareInt($value);
    }

    protected function castTarget(string $value): string
    {
        return $this->prepareDomain($value);
    }

    public function toArray()
    {
        return [
            'host' => $this->host,
            'ttl' => $this->ttl,
            'class' => $this->class,
            'type' => $this->type,
            'pri' => $this->pri,
            'target' => $this->target,
        ];
    }
}
