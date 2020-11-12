<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\NS;

class NSTest extends TestCase
{
    /** @test */
    public function it_can_parse_string(): void
    {
        $record = NS::parse('spatie.be.              82516   IN      NS      ns1.openprovider.nl.');

        static::assertSame('spatie.be', $record->host());
        static::assertSame(82516, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('NS', $record->type());
        static::assertSame('ns1.openprovider.nl', $record->target());
    }

    /** @test */
    public function it_can_make_from_array(): void
    {
        $record = NS::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 82516,
            'type' => 'NS',
            'target' => 'ns1.openprovider.nl',
        ]);

        static::assertSame('spatie.be', $record->host());
        static::assertSame(82516, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('NS', $record->type());
        static::assertSame('ns1.openprovider.nl', $record->target());
    }

    /** @test */
    public function it_can_transform_to_string(): void
    {
        $record = NS::parse('spatie.be.              82516   IN      NS      ns1.openprovider.nl.');

        static::assertSame("spatie.be.\t\t82516\tIN\tNS\tns1.openprovider.nl.", strval($record));
    }
}
