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
            'host' => $attributes[0] ?? null,
            'ttl' => $attributes[1] ?? null,
            'class' => $attributes[2] ?? null,
            'type' => $attributes[3] ?? null,
            'target' => $attributes[4] ?? null,
        ]);
    }

    public function __toString(): string
    {
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->target}.";
    }

    protected function castTarget(?string $value): string
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
            'target' => $this->target,
        ];
    }
}
