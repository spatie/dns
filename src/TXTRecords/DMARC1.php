<?php

namespace Spatie\Dns\TXTRecords;

class DMARC1 extends V {
    
    protected string $p;
    protected array $rua;
    protected array $ruf;
    protected string $sp;
    protected int $pct;
    protected string $fo;

    function __construct(string $value)
    {
        $this->type = 'DMARC';
        $this->version = 1;
        $this->p = $this->cast('p',$value);
        $this->rua = $this->cast('rua',$value);
        $this->ruf = $this->cast('ruf',$value);
        $this->sp = $this->cast('sp',$value);
        $this->pct = $this->cast('pct',$value);
        $this->fo = $this->cast('fo',$value);
    }

    function castP(string $value): string
    {
        preg_match('/p=(none|quarantine|reject)/', $value, $matches);
        if(count($matches) < 2){
            return "";
        }
        return str_replace(";", "",$this->prepareText($matches[1]));
    }
    
    function castRua(string $value): array
    {
        preg_match("/rua=([^;]*)(?:;|$)/i", $value, $matches);
        if (isset($matches[1])) {
            $emails = preg_split("/\s*,/", $matches[1]);
            foreach ($emails as $key => $email) {
                $emails[$key] = $this->prepareText($email);
            }
        }
        else {
            return array();
        }
        return $emails;
    }

    function castRuf(string $value): array
    {
        preg_match("/ruf=([^;]*)(?:;|$)/i", $value, $matches);
        if (isset($matches[1])) {
            $emails = preg_split("/\s*,/", $matches[1]);
            foreach ($emails as $key => $email) {
                $emails[$key] = $this->prepareText($email);
            }
        }
        else {
            return array();
        }
        return $emails;
    }

    function castSp(string $value): string
    {
        preg_match('/sp=(none|quarantine|reject)/', $value, $matches);
        if(count($matches) < 2){
            return "";
        }
        return str_replace(";", "",$this->prepareText($matches[1]));
    }

    function castPct(string $value): int
    {
        preg_match('/pct=([0-9]{1-3}+)/', $value, $matches);
        if(count($matches) < 2){
            return 100;
        }
        return str_replace(";", "",$this->prepareInt($matches[1]));
    }

    function castFo(string $value): string
    {
        preg_match('/fo=([a-zA-Z0-9:]+)/', $value, $matches);
        if(count($matches) < 2){
            return "";
        }
        return str_replace(";", "",$this->prepareText($matches[1]));
    }
}