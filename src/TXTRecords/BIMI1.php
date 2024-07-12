<?php

namespace Spatie\Dns\TXTRecords;

class BIMI1 extends V {
    
    protected string $l;
    protected string $a;

    function __construct(string $value)
    {
        $this->type = 'BIMI';
        $this->version = 1;
        $this->l = $this->cast('l',$value);
        $this->a = $this->cast('a',$value);
    }

    function castL(string $value): string
    {
        preg_match('/l=(https\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}\/\S*)?/', $value, $matches);
        if(count($matches) < 2){
            return "";
        }
        return str_replace(";", "",$this->prepareText($matches[1]));
    }
    
    function castA(string $value): string
    {
        preg_match('/a=(https\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}\/\S*)?/', $value, $matches);
        if(count($matches) < 2){
            return "";
        }
        return str_replace(";", "",$this->prepareText($matches[1]));
    }
}