<?php

use Spatie\Dns\Records\A;

it('can parse string', function () {
    $record = A::parse('spatie.be.              900     IN      A       138.197.187.74');

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(900);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('A');
    expect($record->ip())->toBe('138.197.187.74');
});

it('can make from array', function () {
    $record = A::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 900,
        'type' => 'A',
        'ip' => '138.197.187.74',
    ]);

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(900);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('A');
    expect($record->ip())->toBe('138.197.187.74');
});

it('can transform to string', function () {
    $record = A::parse('spatie.be.              900     IN      A       138.197.187.74');

    expect(strval($record))->toBe("spatie.be.\t\t900\tIN\tA\t138.197.187.74");
});

it('can be converted to an array', function () {
    $record = A::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 900,
        'type' => 'A',
        'ip' => '138.197.187.74',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('spatie.be');
    expect($data['ttl'])->toBe(900);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('A');
    expect($data['ip'])->toBe('138.197.187.74');
});

it('returns null for too few attributes', function () {
    $record = A::parse('spatie.be.              900     IN      A');

    expect($record)->toBeNull();
});
