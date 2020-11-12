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
            'host' => trim($attributes[0], '.'),
            'ttl' => intval($attributes[1]),
            'class' => $attributes[2],
            'type' => mb_strtoupper($attributes[3]),
            'mname' => trim($attributes[4], '.'),
            'rname' => trim($attributes[5], '.'),
            'serial' => intval($attributes[6]),
            'refresh' => intval($attributes[7]),
            'retry' => intval($attributes[8]),
            'expire' => intval($attributes[9]),
            'minimum-ttl' => intval($attributes[10]),
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
}
