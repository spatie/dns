<?php

namespace Spatie\Dns\Records;

use Spatie\Dns\TXTRecords;

/**
 * @method string txt()
 * @method object v()
 */
class TXT extends Record
{
    protected string $txt;
    protected ?object $v;

    public static function parse(string $line): ?self
    {
        $attributes = static::lineToArray($line, 6);

        if (count($attributes) < 6) {
            return null;
        }

        return static::make([
            'host' => $attributes[0],
            'ttl' => $attributes[1],
            'class' => $attributes[2],
            'type' => $attributes[3],
            'txt' => $attributes[4],
            'v' => $attributes[5]
        ]);
    }

    public function __toString(): string
    {
        return "{$this->host}.\t\t{$this->ttl}\t{$this->class}\t{$this->type}\t\"{$this->txt}\"";
    }

    protected function castTxt(string $value): array
    {
        return array('txt' => $this->prepareText($value), 'v' => $this->castV($value));
    }

    protected function castV(string $value): ?object
    {
        preg_match('/v=([a-zA-Z0-9]+);?\W(.*)/', $value, $matches);
        if (empty($matches) || count($matches) < 3){
            return null;
        }
        $v = "Spatie\\Dns\\TXTRecords\\".mb_strtoupper($matches[1]);
        return new $v($matches[2]);
    }

    public function toArray()
    {
        return [
            'host' => $this->host,
            'ttl' => $this->ttl,
            'class' => $this->class,
            'type' => $this->type,
            'txt' => $this->txt,
            'v' => $this->v
        ];
    }
}
