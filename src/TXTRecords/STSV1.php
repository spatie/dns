<?php

namespace Spatie\Dns\TXTRecords;

class STSV1 extends V {
    
    public int $id;

    function __construct(string $value)
    {
        $this->type = 'STS';
        $this->version = 1;
        $this->id = $this->cast('id',$value);
    }

    function castId(string $value): int
    {
        preg_match('/id=([0-9]+)/', $value, $matches);
        return $matches[1];
    }
}