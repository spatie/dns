<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\MX;
use Spatie\Dns\Records\NS;
use Spatie\Dns\Records\SOA;

class SOATest extends TestCase
{
    /** @test */
    public function it_can_parse_string(): void
    {
        $record = SOA::parse('spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800 3600');

        static::assertSame('spatie.be', $record->host());
        static::assertSame(82393, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('SOA', $record->type());
        static::assertSame('ns1.openprovider.nl', $record->mname());
        static::assertSame('dns.openprovider.eu', $record->rname());
        static::assertSame(2020100801, $record->serial());
        static::assertSame(10800, $record->refresh());
        static::assertSame(3600, $record->retry());
        static::assertSame(604800, $record->expire());
        static::assertSame(3600, $record->minimum_ttl());
    }

    /** @test */
    public function it_can_make_from_array(): void
    {
        $record = SOA::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 82393,
            'type' => 'SOA',
            'mname' => 'ns1.openprovider.nl',
            'rname' => 'dns.openprovider.eu',
            'serial' => 2020100801,
            'refresh' => 10800,
            'retry' => 3600,
            'expire' => 604800,
            'minimum_ttl' => 3600,
        ]);

        static::assertSame('spatie.be', $record->host());
        static::assertSame(82393, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('SOA', $record->type());
        static::assertSame('ns1.openprovider.nl', $record->mname());
        static::assertSame('dns.openprovider.eu', $record->rname());
        static::assertSame(2020100801, $record->serial());
        static::assertSame(10800, $record->refresh());
        static::assertSame(3600, $record->retry());
        static::assertSame(604800, $record->expire());
        static::assertSame(3600, $record->minimum_ttl());
    }

    /** @test */
    public function it_can_transform_to_string(): void
    {
        $record = SOA::parse('spatie.be.              82393   IN      SOA     ns1.openprovider.nl. dns.openprovider.eu. 2020100801 10800 3600 604800 3600');

        static::assertSame("spatie.be.\t\t82393\tIN\tSOA\tns1.openprovider.nl.\tdns.openprovider.eu.\t2020100801\t10800\t3600\t604800\t3600", strval($record));
    }
}
