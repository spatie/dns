<?php

use Spatie\Dns\Dns;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\Record;

afterEach(function () {
    // Always restore the global fake between tests to avoid cross-test pollution
    Dns::restore();
});

it('can fake dns lookups using a simple map', function () {
    Dns::fake([
        'example.com' => [
            'A' => [
                // String will be parsed via Factory
                "example.com.\t300\tIN\tA\t203.0.113.10",
                // Array will be passed to Factory::make
                ['host' => 'example.com.', 'ttl' => 300, 'class' => 'IN', 'type' => 'A', 'ip' => '203.0.113.11'],
            ],
        ],
    ]);

    $records = (new Dns())->getRecords('example.com', 'A');

    expect($records)->toHaveCount(2)
        ->and($records[0])->toBeInstanceOf(Record::class)
        ->and($records[0])->toBeInstanceOf(A::class)
        ->and(in_array($records[0]->ip(), ['203.0.113.10', '203.0.113.11'], true))->toBeTrue();
});

it('can fake dns lookups using a callable', function () {
    Dns::fake(function (string $domain, array $types, $factory) {
        return [
            'A' => [
                $factory->make('A', [
                    'host' => $domain . '.',
                    'ttl' => 60,
                    'class' => 'IN',
                    'type' => 'A',
                    'ip' => '127.0.0.1',
                ]),
            ],
        ];
    });

    $records = (new Dns())->getRecords('my.test', 'A');

    expect($records)->toHaveCount(1)
        ->and($records[0])->toBeInstanceOf(A::class)
        ->and($records[0]->ip())->toBe('127.0.0.1');
});

it('supports wildcard type responses in map mode', function () {
    Dns::fake([
        'example.org' => [
            '*' => [
                // Will be returned for any type when a specific type key is not defined
                ['host' => 'example.org.', 'ttl' => 120, 'class' => 'IN', 'type' => 'MX', 'pri' => 10, 'target' => 'mail.example.org.'],
            ],
        ],
    ]);

    $mx = (new Dns())->getRecords('example.org', 'MX');

    expect($mx)->toHaveCount(1)
        ->and($mx[0])->toBeInstanceOf(MX::class)
        ->and($mx[0]->target())->toBe('mail.example.org');
});

it('supports wildcard domain responses in map mode', function () {
    Dns::fake([
        '*' => [
            'A' => [
                ['host' => 'any.tld.', 'ttl' => 60, 'class' => 'IN', 'type' => 'A', 'ip' => '198.51.100.5'],
            ],
        ],
    ]);

    $records = (new Dns())->getRecords('another.test', 'A');

    expect($records)->toHaveCount(1)
        ->and($records[0])->toBeInstanceOf(A::class)
        ->and($records[0]->ip())->toBe('198.51.100.5');
});

it('toggles isFaked and can be restored', function () {
    expect(Dns::isFaked())->toBeFalse();

    Dns::fake([
        'x.test' => [ 'A' => [] ],
    ]);

    expect(Dns::isFaked())->toBeTrue();

    Dns::restore();

    expect(Dns::isFaked())->toBeFalse();
});
