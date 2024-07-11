<?php

namespace Spatie\Dns\TXTRecords;

class DMARC1 extends V {
    
    public string $p;
    public array $rua;
    public array $ruf;
    public string $sp;
    public string $fo;

    function __construct(string $value)
    {
        $this->type = 'DMARC';
        $this->version = 1;
        $this->p = $this->cast('p',$value);
        $this->rua = $this->cast('rua',$value);
        $this->ruf = $this->cast('ruf',$value);
        $this->sp = $this->cast('sp',$value);
        $this->fo = $this->cast('fo',$value);
    }

    function castP(string $value): string
    {
        preg_match('/p=(none|quarantine|reject)/', $value, $matches);
        if(count($matches) < 2){
            return "";
        }
        return str_replace(";", "",$matches[1]);
    }
    
    function castRua(string $value): array
    {
        preg_match("/rua=([^;]*)(?:;|$)/i", $value, $matches);
        if (isset($matches[1])) {
            $emails = preg_split("/\s*,/", $matches[1]);
        }
        else {
            return "";
        }
        return $emails;
    }

    function castRuf(string $value): array
    {
        preg_match("/ruf=([^;]*)(?:;|$)/i", $value, $matches);
        if (isset($matches[1])) {
            $emails = preg_split("/\s*,/", $matches[1]);
        }
        else {
            return "";
        }
        return $emails;
    }

    function castSp(string $value): string
    {
        preg_match('/sp=(none|quarantine|reject)/', $value, $matches);
        if(count($matches) < 2){
            return "";
        }
        return str_replace(";", "",$matches[1]);
    }

    function castFo(string $value): string
    {
        preg_match('/fo=([a-zA-Z0-9:]+)/', $value, $matches);
        if(count($matches) < 2){
            return "";
        }
        return str_replace(";", "",$matches[1]);
    }
}