<?php

namespace Spatie\Dns\TXTRecords;

class SPF1 extends V {
    
    public string $value;

    function __construct(string $value)
    {
        $this->type = 'SPF';
        $this->version = 1;
        $this->value = $this->cast('value',$value);
    }
}