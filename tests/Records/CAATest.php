<?php

use Spatie\Dns\Records\CAA;

it('can parse string', function () {
    $record = CAA::parse('google.com.             86400   IN      CAA     0 issue "pki.goog"');

    expect($record->host())->toBe('google.com');
    expect($record->ttl())->toBe(86400);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('CAA');
    expect($record->flags())->toBe(0);
    expect($record->tag())->toBe('issue');
    expect($record->value())->toBe('pki.goog');
});

it('can make from array', function () {
    $record = CAA::make([
        'host' => 'google.com',
        'class' => 'IN',
        'ttl' => 86400,
        'type' => 'CAA',
        'flags' => 0,
        'tag' => 'issue',
        'value' => 'pki.goog',
    ]);

    expect($record->host())->toBe('google.com');
    expect($record->ttl())->toBe(86400);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('CAA');
    expect($record->flags())->toBe(0);
    expect($record->tag())->toBe('issue');
    expect($record->value())->toBe('pki.goog');
});

it('can transform to string', function () {
    $record = CAA::parse('google.com.             86400   IN      CAA     0 issue "pki.goog"');

    expect(strval($record))->toBe("google.com.\t\t86400\tIN\tCAA\t0\tissue\t\"pki.goog\"");
});

it('can be converted to an array', function () {
    $record = CAA::make([
        'host' => 'google.com',
        'class' => 'IN',
        'ttl' => 86400,
        'type' => 'CAA',
        'flags' => 0,
        'tag' => 'issue',
        'value' => 'pki.goog',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('google.com');
    expect($data['ttl'])->toBe(86400);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('CAA');
    expect($data['flags'])->toBe(0);
    expect($data['tag'])->toBe('issue');
    expect($data['value'])->toBe('pki.goog');
});

it('returns null for too few attributes', function () {
    $record = CAA::parse('google.com.             86400   IN      CAA     0 issue');

    expect($record)->toBeNull();
});
