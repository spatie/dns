<?php

namespace Spatie\Dns\Records;

/**
 * @method string reversDnsName()
 */
class PTR extends Record
{
    protected string $reversDnsName;
    protected string $name;

    public static function parse(string $line): ?self
    {
        $attributes = static::lineToArray($line, 5);

        if (count($attributes) < 5) {
            return null;
        }

        return static::make([
            'reversDnsName' => $attributes[0],
            'ttl' => $attributes[1],
            'class' => $attributes[2],
            'type' => $attributes[3],
            'name' => $attributes[4],
        ]);
    }

    public function __toString(): string
    {
        return "{$this->reversDnsName}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->name}";
    }

    public function toArray()
    {
        return [
            'reversDnsName' => $this->reversDnsName,
            'ttl' => $this->ttl,
            'class' => $this->class,
            'type' => $this->type,
            'name' => $this->name,
        ];
    }

    protected function castReversDnsName(string $value): string
    {
        return $this->prepareDomain($value);
    }
}
