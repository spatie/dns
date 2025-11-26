<?php

use Spatie\Dns\Records\PTR;

dataset('rDns', [
    ['1.73.1.5.in-addr.arpa.', '1.73.1.5.in-addr.arpa'],
    ['1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.1.0.0.0.0.c.f.6.7.0.a.2.ip6.arpa.', '1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.1.0.0.0.0.c.f.6.7.0.a.2.ip6.arpa'],
]);

it('can parse string', function (string $rDNS, string $trimmedRDNS) {
    $record = PTR::parse($rDNS.'              3600     IN      PTR       ae0.452.fra.as205948.creoline.net.');

    expect($record->host())->toBe($trimmedRDNS);
    expect($record->ttl())->toBe(3600);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('PTR');
    expect($record->target())->toBe('ae0.452.fra.as205948.creoline.net.');
})->with('rDns');

it('can make from array', function (string $rDNS, string $trimmedRDNS) {
    $record = PTR::make([
        'host' => $rDNS,
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'PTR',
        'target' => 'ae0.452.fra.as205948.creoline.net.',
    ]);

    expect($record->host())->toBe($trimmedRDNS);
    expect($record->ttl())->toBe(3600);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('PTR');
    expect($record->target())->toBe('ae0.452.fra.as205948.creoline.net.');
})->with('rDns');

it('can transform to string', function (string $rDNS) {
    $record = PTR::parse($rDNS.'              3600     IN      PTR       ae0.452.fra.as205948.creoline.net.');

    expect(strval($record))->toBe($rDNS."\t\t3600\tIN\tPTR\tae0.452.fra.as205948.creoline.net.");
})->with('rDns');

it('can be converted to an array', function (string $rDNS, string $trimmedRDNS) {
    $record = PTR::make([
        'host' => $rDNS,
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'PTR',
        'target' => 'ae0.452.fra.as205948.creoline.net.',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe($trimmedRDNS);
    expect($data['ttl'])->toBe(3600);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('PTR');
    expect($data['target'])->toBe('ae0.452.fra.as205948.creoline.net.');
})->with('rDns');

it('returns null for too few attributes', function (string $rDNS) {
    $record = PTR::parse($rDNS.'              3600     IN      PTR');

    expect($record)->toBeNull();
})->with('rDns');
