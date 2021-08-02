<?php

namespace Spatie\Dns\Records;

/**
 * @method string mname()
 * @method string rname()
 * @method int serial()
 * @method int refresh()
 * @method int retry()
 * @method int expire()
 * @method int minimum_ttl()
 */
class SOA extends Record
{
    protected string $mname;
    protected string $rname;
    protected int $serial;
    protected int $refresh;
    protected int $retry;
    protected int $expire;
    protected int $minimum_ttl;

    public static function parse(string $line): ?self
    {
        $attributes = static::lineToArray($line, 11);

        if (count($attributes) < 11) {
            return null;
        }

        return static::make([
            'host' => $attributes[0],
            'ttl' => $attributes[1],
            'class' => $attributes[2],
            'type' => $attributes[3],
            'mname' => $attributes[4],
            'rname' => $attributes[5],
            'serial' => $attributes[6],
            'refresh' => $attributes[7],
            'retry' => $attributes[8],
            'expire' => $attributes[9],
            'minimum-ttl' => $attributes[10],
        ]);
    }

    public function __toString(): string
    {
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t{$this->mname}.\t{$this->rname}.\t{$this->serial}\t{$this->refresh}\t{$this->retry}\t{$this->expire}\t{$this->minimum_ttl}";
    }

    protected function castMname(string $value): string
    {
        return $this->prepareDomain($value);
    }

    protected function castRname(string $value): string
    {
        return $this->prepareDomain($value);
    }

    protected function castSerial($value): int
    {
        return $this->prepareInt($value);
    }

    protected function castRefresh($value): int
    {
        return $this->prepareInt($value);
    }

    protected function castRetry($value): int
    {
        return $this->prepareInt($value);
    }

    protected function castExpire($value): int
    {
        return $this->prepareInt($value);
    }

    protected function castMinimumTtl($value): int
    {
        return $this->prepareInt($value);
    }

    public function toArray()
    {
        return [
            'host' => $this->host,
            'ttl' => $this->ttl,
            'class' => $this->class,
            'type' => $this->type,
            'mname' => $this->mname,
            'rname' => $this->rname,
            'serial' => $this->serial,
            'refresh' => $this->refresh,
            'retry' => $this->retry,
            'expire' => $this->expire,
            'minimum_ttl' => $this->minimum_ttl,
        ];
    }
}
