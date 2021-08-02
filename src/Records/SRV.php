<?php

namespace Spatie\Dns\Records;

/**
 * @method int pri()
 * @method int weight()
 * @method string target()
 * @method int port()
 */
class SRV extends Record
{
    protected int $pri;
    protected int $weight;
    protected string $target;
    protected int $port;

    public static function parse(string $line): self
    {
        $attributes = static::lineToArray($line, 8);

        return static::make([
            'host' => $attributes[0] ?? null,
            'ttl' => $attributes[1] ?? null,
            'class' => $attributes[2] ?? null,
            'type' => $attributes[3] ?? null,
            'pri' => $attributes[4] ?? null,
            'weight' => $attributes[5] ?? null,
            'port' => $attributes[6] ?? null,
            'target' => $attributes[7] ?? null,
        ]);
    }

    public function __toString(): string
    {
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->pri}\t{$this->weight}\t{$this->port}\t{$this->target}.";
    }

    protected function castPri($value): int
    {
        return $this->prepareInt($value);
    }

    protected function castWeight($value): int
    {
        return $this->prepareInt($value);
    }

    protected function castPort($value): int
    {
        return $this->prepareInt($value);
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
            'pri' => $this->pri,
            'weight' => $this->weight,
            'port' => $this->port,
            'target' => $this->target,
        ];
    }
}
