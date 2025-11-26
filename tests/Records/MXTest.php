<?php

use Spatie\Dns\Records\MX;

it('can parse string', function () {
    $record = MX::parse('spatie.be.              1665    IN      MX      10 ASPMX.L.GOOGLE.COM.');

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(1665);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('MX');
    expect($record->pri())->toBe(10);
    expect($record->target())->toBe('aspmx.l.google.com');
});

it('can make from array', function () {
    $record = MX::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 1665,
        'type' => 'MX',
        'pri' => 10,
        'target' => 'ASPMX.L.GOOGLE.COM',
    ]);

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(1665);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('MX');
    expect($record->pri())->toBe(10);
    expect($record->target())->toBe('aspmx.l.google.com');
});

it('can transform to string', function () {
    $record = MX::parse('spatie.be.              1665    IN      MX      10 ASPMX.L.GOOGLE.COM.');

    expect(strval($record))->toBe("spatie.be.\t\t1665\tIN\tMX\t10\taspmx.l.google.com.");
});

it('can be converted to an array', function () {
    $record = MX::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 1665,
        'type' => 'MX',
        'pri' => 10,
        'target' => 'ASPMX.L.GOOGLE.COM',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('spatie.be');
    expect($data['ttl'])->toBe(1665);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('MX');
    expect($data['pri'])->toBe(10);
    expect($data['target'])->toBe('aspmx.l.google.com');
});

it('returns null for too few attributes', function () {
    $record = MX::parse('spatie.be.              1665    IN      MX      10');

    expect($record)->toBeNull();
});
