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

    public static function parse(string $line): self
    {
        $attributes = static::lineToArray($line, 11);

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
        return sprintf(
            "%s.\t\t%d\t%s\t%s\t%s.\t%s.\t%d\t%d\t%d\t%d\t%d",
            $this->host,
            $this->ttl,
            $this->class,
            $this->type,
            $this->mname,
            $this->rname,
            $this->serial,
            $this->refresh,
            $this->retry,
            $this->expire,
            $this->minimum_ttl
        );
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
}
