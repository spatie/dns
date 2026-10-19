<?php

namespace Spatie\Dns\Records;

/**
 * @method int keytag()
 * @method int algorithm()
 * @method int digesttype()
 * @method string digest()
 */
class DS extends Record
{
    protected int $keytag;

    protected int $algorithm;

    protected int $digesttype;

    protected string $digest;

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
            'keytag' => $attributes[4],
            'algorithm' => $attributes[5],
            'digesttype' => $attributes[6],
            'digest' => $attributes[7],
        ]);
    }

    public function __toString(): string
    {
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->keytag}\t{$this->algorithm}\t{$this->digesttype}\t{$this->digest}";
    }

    protected function castDigest(string $value): string
    {
        return str_replace(' ', '', $value);
    }

    /**
     * @return array{
     *     host: string,
     *     ttl: int,
     *     class: string,
     *     type: string,
     *     keytag: int,
     *     algorithm: int,
     *     digesttype: int,
     *     digest: string,
     * }
     */
    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'ttl' => $this->ttl,
            'class' => $this->class,
            'type' => $this->type,
            'keytag' => $this->keytag,
            'algorithm' => $this->algorithm,
            'digesttype' => $this->digesttype,
            'digest' => $this->digest,
        ];
    }
}
