<?php

namespace Spatie\Dns\TXTRecords;

class TLSRPTV1 extends V {
    
    public array $rua;

    function __construct(string $value)
    {
        $this->type = 'TLSRPT';
        $this->version = 1;
        $this->rua = $this->cast('rua',$value);
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
}