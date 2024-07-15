<?php

namespace Spatie\Dns\TXTRecords;

class TLSRPTV1 extends V {
    
    protected array $rua;

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
            foreach ($emails as $key => $email) {
                $emails[$key] = $this->prepareText($email);
            }
        }
        else {
            return array();
        }
        return $emails;
    }
}