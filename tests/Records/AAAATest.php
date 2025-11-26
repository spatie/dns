<?php

use Spatie\Dns\Records\AAAA;

it('can parse string', function () {
    $record = AAAA::parse('google.com.             900     IN      AAAA    2a00:1450:400e:800::200e');

    expect($record->host())->toBe('google.com');
    expect($record->ttl())->toBe(900);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('AAAA');
    expect($record->ipv6())->toBe('2a00:1450:400e:800::200e');
});

it('can make from array', function () {
    $record = AAAA::make([
        'host' => 'google.com',
        'class' => 'IN',
        'ttl' => 900,
        'type' => 'AAAA',
        'ipv6' => '2a00:1450:400e:800::200e',
    ]);

    expect($record->host())->toBe('google.com');
    expect($record->ttl())->toBe(900);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('AAAA');
    expect($record->ipv6())->toBe('2a00:1450:400e:800::200e');
});

it('can transform to string', function () {
    $record = AAAA::parse('google.com.             900     IN      AAAA    2a00:1450:400e:800::200e');

    expect(strval($record))->toBe("google.com.\t\t900\tIN\tAAAA\t2a00:1450:400e:800::200e");
});

it('can be converted to an array', function () {
    $record = AAAA::make([
        'host' => 'google.com',
        'class' => 'IN',
        'ttl' => 900,
        'type' => 'AAAA',
        'ipv6' => '2a00:1450:400e:800::200e',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('google.com');
    expect($data['ttl'])->toBe(900);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('AAAA');
    expect($data['ipv6'])->toBe('2a00:1450:400e:800::200e');
});

it('returns null for too few attributes', function () {
    $record = AAAA::parse('google.com.             900     IN      AAAA');

    expect($record)->toBeNull();
});
