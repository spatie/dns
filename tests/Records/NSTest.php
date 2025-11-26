<?php

use Spatie\Dns\Records\NS;

it('can parse string', function () {
    $record = NS::parse('spatie.be.              82516   IN      NS      ns1.openprovider.nl.');

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(82516);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('NS');
    expect($record->target())->toBe('ns1.openprovider.nl');
});

it('can make from array', function () {
    $record = NS::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 82516,
        'type' => 'NS',
        'target' => 'ns1.openprovider.nl',
    ]);

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(82516);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('NS');
    expect($record->target())->toBe('ns1.openprovider.nl');
});

it('can transform to string', function () {
    $record = NS::parse('spatie.be.              82516   IN      NS      ns1.openprovider.nl.');

    expect(strval($record))->toBe("spatie.be.\t\t82516\tIN\tNS\tns1.openprovider.nl.");
});

it('can be converted to an array', function () {
    $record = NS::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 82516,
        'type' => 'NS',
        'target' => 'ns1.openprovider.nl',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('spatie.be');
    expect($data['ttl'])->toBe(82516);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('NS');
    expect($data['target'])->toBe('ns1.openprovider.nl');
});

it('returns null for too few attributes', function () {
    $record = NS::parse('spatie.be.              82516   IN      NS');

    expect($record)->toBeNull();
});
