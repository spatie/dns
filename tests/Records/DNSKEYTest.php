<?php

use Spatie\Dns\Records\DNSKEY;

it('can parse string', function () {
    $record = DNSKEY::parse('icann.com.              3600    IN      DNSKEY  256 3 13 Up+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEj dbdUrRHYYec/ke+5YTHvhX7pkIffjg==');

    expect($record->host())->toBe('icann.com');
    expect($record->ttl())->toBe(3600);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('DNSKEY');
    expect($record->flags())->toBe(256);
    expect($record->protocol())->toBe(3);
    expect($record->algorithm())->toBe(13);
    expect($record->publickey())->toBe('Up+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEjdbdUrRHYYec/ke+5YTHvhX7pkIffjg==');
});

it('can make from array', function () {
    $record = DNSKEY::make([
        'host' => 'icann.com',
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'DNSKEY',
        'flags' => 256,
        'protocol' => 3,
        'algorithm' => 13,
        'publickey' => 'Up+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEjdbdUrRHYYec/ke+5YTHvhX7pkIffjg==',
    ]);

    expect($record->host())->toBe('icann.com');
    expect($record->ttl())->toBe(3600);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('DNSKEY');
    expect($record->flags())->toBe(256);
    expect($record->protocol())->toBe(3);
    expect($record->algorithm())->toBe(13);
    expect($record->publickey())->toBe('Up+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEjdbdUrRHYYec/ke+5YTHvhX7pkIffjg==');
});

it('can transform to string', function () {
    $record = DNSKEY::parse('icann.com.              3600    IN      DNSKEY  256 3 13 Up+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEj dbdUrRHYYec/ke+5YTHvhX7pkIffjg==');

    expect(strval($record))->toBe("icann.com.\t\t3600\tIN\tDNSKEY\t256\t3\t13\tUp+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEjdbdUrRHYYec/ke+5YTHvhX7pkIffjg==");
});

it('can be converted to an array', function () {
    $record = DNSKEY::make([
        'host' => 'icann.com',
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'DNSKEY',
        'flags' => 256,
        'protocol' => 3,
        'algorithm' => 13,
        'publickey' => 'Up+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEjdbdUrRHYYec/ke+5YTHvhX7pkIffjg==',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('icann.com');
    expect($data['ttl'])->toBe(3600);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('DNSKEY');
    expect($data['flags'])->toBe(256);
    expect($data['protocol'])->toBe(3);
    expect($data['algorithm'])->toBe(13);
    expect($data['publickey'])->toBe('Up+opSl3nHqfqgOgWKBduzNY/18thL7BcDRIxtkdDl3C/LVIOyjy5lEjdbdUrRHYYec/ke+5YTHvhX7pkIffjg==');
});

it('returns null for too few attributes', function () {
    $record = DNSKEY::parse('icann.com.              3600    IN      DNSKEY  256 3 13');

    expect($record)->toBeNull();
});
