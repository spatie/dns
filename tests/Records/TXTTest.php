<?php

use Spatie\Dns\Records\TXT;

it('can parse string', function () {
    $record = TXT::parse('spatie.be.              594     IN      TXT     "v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all"');

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(594);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('TXT');
    expect($record->txt())->toBe('v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all');
});

it('can parse long txt string', function () {
    $record = TXT::parse('spatie.be.              594     IN      TXT     "v=spf1 a mx ip4:100.101.102.103 ip4:104.105.106.107 ip4:108.109.110.111 ip6:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a include:_spf.google.com include:_spf.c" "reatesend.com ~all"');

    expect($record->txt())->toBe('v=spf1 a mx ip4:100.101.102.103 ip4:104.105.106.107 ip4:108.109.110.111 ip6:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a include:_spf.google.com include:_spf.createsend.com ~all');
});

it('can make from array', function () {
    $record = TXT::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 594,
        'type' => 'TXT',
        'txt' => 'v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all',
    ]);

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(594);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('TXT');
    expect($record->txt())->toBe('v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all');
});

it('can transform to string', function () {
    $record = TXT::parse('spatie.be.              594     IN      TXT     "v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all"');

    expect(strval($record))->toBe("spatie.be.\t\t594\tIN\tTXT\t\"v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all\"");
});

it('can be converted to an array', function () {
    $record = TXT::make([
        'host' => 'spatie.be',
        'class' => 'IN',
        'ttl' => 594,
        'type' => 'TXT',
        'txt' => 'v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all',
    ]);

    $data = $record->toArray();
    expect($data['host'])->toBe('spatie.be');
    expect($data['ttl'])->toBe(594);
    expect($data['class'])->toBe('IN');
    expect($data['type'])->toBe('TXT');
    expect($data['txt'])->toBe('v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all');
});

it('returns null for too few attributes', function () {
    $record = TXT::parse('spatie.be.              594     IN      TXT');

    expect($record)->toBeNull();
});

it('can parse a string with double space', function () {
    $record = TXT::parse('spatie.be.              594     IN      TXT     "test 2  7"');

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(594);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('TXT');
    expect($record->txt())->toBe('test 2  7');
});

it('can parse a string with a double quote', function () {
    $record = TXT::parse('spatie.be.              594     IN      TXT     "test \""');

    expect($record->host())->toBe('spatie.be');
    expect($record->ttl())->toBe(594);
    expect($record->class())->toBe('IN');
    expect($record->type())->toBe('TXT');
    expect($record->txt())->toBe('test \\"');
});
