<?php

use Spatie\Dns\Records\SRV;

it('can parse string', function () {
    $record = SRV::parse('_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80 mxtoolbox.com.');

    expect($record->host())->toBe('_http._tcp.mxtoolbox.com');
    expect($record->ttl())->toBe(3600);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('SRV');
    expect($record->pri())->toBe(10);
    expect($record->weight())->toBe(100);
    expect($record->port())->toBe(80);
    expect($record->target())->toBe('mxtoolbox.com');
});

it('can make from array', function () {
    $record = SRV::make([
        'host' => '_http._tcp.mxtoolbox.com',
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'SRV',
        'pri' => 10,
        'weight' => 100,
        'port' => 80,
        'target' => 'mxtoolbox.com',
    ]);

    expect($record->host())->toBe('_http._tcp.mxtoolbox.com');
    expect($record->ttl())->toBe(3600);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('SRV');
    expect($record->pri())->toBe(10);
    expect($record->weight())->toBe(100);
    expect($record->port())->toBe(80);
    expect($record->target())->toBe('mxtoolbox.com');
});

it('can transform to string', function () {
    $record = SRV::parse('_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80 mxtoolbox.com.');

    expect(strval($record))->toBe("_http._tcp.mxtoolbox.com.\t\t3600\tIN\tSRV\t10\t100\t80\tmxtoolbox.com.");
});

it('can be converted to an array', function () {
    $record = SRV::make([
        'host' => '_http._tcp.mxtoolbox.com',
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'SRV',
        'pri' => 10,
        'weight' => 100,
        'port' => 80,
        'target' => 'mxtoolbox.com',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('_http._tcp.mxtoolbox.com');
    expect($data['ttl'])->toBe(3600);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('SRV');
    expect($data['pri'])->toBe(10);
    expect($data['weight'])->toBe(100);
    expect($data['port'])->toBe(80);
    expect($data['target'])->toBe('mxtoolbox.com');
});

it('returns null for too few attributes', function () {
    $record = SRV::parse('_http._tcp.mxtoolbox.com. 3600  IN      SRV     10 100 80');

    expect($record)->toBeNull();
});
