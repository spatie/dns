<?php

namespace Spatie\Dns\Support;

class Domain
{
    protected string $domain;

    public function __construct(string $input)
    {
        $this->domain = $this->sanitize($input);
    }

    public function __toString(): string
    {
        return $this->domain;
    }

    protected function sanitize(string $url): string
    {
        if (preg_match('/^(\w+:\/\/).*$/', $url) === 0) {
            $url = 'https://'.$url;
        }

        return parse_url($url, PHP_URL_HOST);
    }
}
