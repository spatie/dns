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
            'host' => $attributes[0] ?? null,
            'ttl' => $attributes[1] ?? null,
            'class' => $attributes[2] ?? null,
            'type' => $attributes[3] ?? null,
            'flags' => $attributes[4] ?? null,
            'tag' => $attributes[5] ?? null,
            'value' => $attributes[6] ?? null,
        ]);
    }

    public function __toString(): string
    {
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->flags}\t{$this->tag}\t\"{$this->value}\"";
    }

    protected function castFlags(?string $value): int
    {
        return $this->prepareInt($value);
    }

    protected function castValue(?string $value): string
    {
        return $this->prepareText($value);
    }

    public function toArray()
    {
        return [
            'host' => $this->host,
            'ttl' => $this->ttl,
            'class' => $this->class,
            'type' => $this->type,
            'flags' => $this->flags,
            'tag' => $this->tag,
            'value' => $this->value,
        ];
    }
}
