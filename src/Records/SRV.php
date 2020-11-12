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
            'host' => trim($attributes[0], '.'),
            'ttl' => intval($attributes[1]),
            'class' => $attributes[2],
            'type' => mb_strtoupper($attributes[3]),
            'pri' => intval($attributes[4]),
            'weight' => intval($attributes[5]),
            'port' => intval($attributes[6]),
            'target' => trim($attributes[7], '.'),
        ]);
    }

    public function __toString(): string
    {
        return sprintf(
            "%s.\t\t%d\t%s\t%s\t%d\t%d\t%d\t%s.",
            $this->host,
            $this->ttl,
            $this->class,
            $this->type,
            $this->pri,
            $this->weight,
            $this->port,
            $this->target
        );
    }
}
