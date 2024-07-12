<?php

namespace Spatie\Dns\TXTRecords;

class SPF1 extends V {
    
    protected array $value;

    function __construct(string $value)
    {
        $this->type = 'SPF';
        $this->version = 1;
        $this->value = $this->cast('value',$value);
    }

    function castValue(string $value): array
    {
        preg_match_all('/([a-zA-Z0-9:\.-~?]+)+/', $value, $matches);
        if(count($matches) < 1){
             return array();
        }
        foreach ($matches[1] as $key => $match) {
            $matches[1][$key] = $this->prepareText($match);
        }
        return str_replace(";", "",$matches[1]);
    }
}