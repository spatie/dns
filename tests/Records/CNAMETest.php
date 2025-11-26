<?php

use Spatie\Dns\Records\CNAME;

it('can parse string', function () {
    $record = CNAME::parse('www.spatie.be.       300     IN      CNAME   spatie.be.');

    expect($record->host())->toBe('www.spatie.be');
    expect($record->ttl())->toBe(300);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('CNAME');
    expect($record->target())->toBe('spatie.be');
});

it('can make from array', function () {
    $record = CNAME::make([
        'host' => 'www.spatie.be',
        'class' => 'IN',
        'ttl' => 300,
        'type' => 'CNAME',
        'target' => 'spatie.be',
    ]);

    expect($record->host())->toBe('www.spatie.be');
    expect($record->ttl())->toBe(300);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('CNAME');
    expect($record->target())->toBe('spatie.be');
});

it('can transform to string', function () {
    $record = CNAME::parse('www.spatie.be.       300     IN      CNAME   spatie.be.');

    expect(strval($record))->toBe("www.spatie.be.\t\t300\tIN\tCNAME\tspatie.be.");
});

it('can be converted to an array', function () {
    $record = CNAME::make([
        'host' => 'www.spatie.be',
        'class' => 'IN',
        'ttl' => 300,
        'type' => 'CNAME',
        'target' => 'spatie.be',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('www.spatie.be');
    expect($data['ttl'])->toBe(300);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('CNAME');
    expect($data['target'])->toBe('spatie.be');
});

it('returns null for too few attributes', function () {
    $record = CNAME::parse('www.spatie.be.       300     IN      CNAME');

    expect($record)->toBeNull();
});
