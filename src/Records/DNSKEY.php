<?php

namespace Spatie\Dns\Records;

/**
 * @method int flags()
 * @method int protocol()
 * @method int algorithm()
 * @method string publickey()
 */
class DNSKEY extends Record
{
    protected int $flags;

    protected int $protocol;

    protected int $algorithm;

    protected string $publickey;

    public static function parse(string $line): ?self
    {
        $attributes = static::lineToArray($line, 8);

        if (count($attributes) < 8) {
            return null;
        }

        return static::make([
            'host' => $attributes[0],
            'ttl' => $attributes[1],
            'class' => $attributes[2],
            'type' => $attributes[3],
            'flags' => $attributes[4],
            'protocol' => $attributes[5],
            'algorithm' => $attributes[6],
            'publickey' => $attributes[7],
        ]);
    }

    public function __toString(): string
    {
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->flags}\t{$this->protocol}\t{$this->algorithm}\t{$this->publickey}";
    }

    protected function castPublickey(string $value): string
    {
        return str_replace(' ', '', $value);
    }

    /** @return array<string, int|string> */
    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'ttl' => $this->ttl,
            'class' => $this->class,
            'type' => $this->type,
            'flags' => $this->flags,
            'protocol' => $this->protocol,
            'algorithm' => $this->algorithm,
            'publickey' => $this->publickey,
        ];
    }
}
