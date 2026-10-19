<?php

use Spatie\Dns\Records\DS;

it('can parse string', function () {
    $record = DS::parse('icann.com.              21600   IN      DS      50326 13 2 48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABD C6F88DC9');

    expect($record->host())->toBe('icann.com');
    expect($record->ttl())->toBe(21600);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('DS');
    expect($record->keytag())->toBe(50326);
    expect($record->algorithm())->toBe(13);
    expect($record->digesttype())->toBe(2);
    expect($record->digest())->toBe('48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABDC6F88DC9');
});

it('can make from array', function () {
    $record = DS::make([
        'host' => 'icann.com',
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'DS',
        'keytag' => 50326,
        'algorithm' => 13,
        'digesttype' => 2,
        'digest' => '48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABDC6F88DC9',
    ]);

    expect($record->host())->toBe('icann.com');
    expect($record->ttl())->toBe(3600);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('DS');
    expect($record->keytag())->toBe(50326);
    expect($record->algorithm())->toBe(13);
    expect($record->digesttype())->toBe(2);
    expect($record->digest())->toBe('48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABDC6F88DC9');
});

it('can transform to string', function () {
    $record = DS::parse('icann.com.              21600   IN      DS      50326 13 2 48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABD C6F88DC9');

    expect(strval($record))->toBe("icann.com.\t\t21600\tIN\tDS\t50326\t13\t2\t48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABDC6F88DC9");
});

it('can be converted to an array', function () {
    $record = DS::make([
        'host' => 'icann.com',
        'class' => 'IN',
        'ttl' => 3600,
        'type' => 'DS',
        'keytag' => 50326,
        'algorithm' => 13,
        'digesttype' => 2,
        'digest' => '48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABDC6F88DC9',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('icann.com');
    expect($data['ttl'])->toBe(3600);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('DS');
    expect($data['keytag'])->toBe(50326);
    expect($data['algorithm'])->toBe(13);
    expect($data['digesttype'])->toBe(2);
    expect($data['digest'])->toBe('48DBDF26A611338821CBC565EF046F48AD1B92D5DE15DA95BB9D1ABDC6F88DC9');
});

it('returns null for too few attributes', function () {
    $record = DS::parse('icann.com.              21600   IN      DS      50326 13 2');

    expect($record)->toBeNull();
});
