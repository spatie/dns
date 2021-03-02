<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\CNAME;

class CNAMETest extends TestCase
{
    /** @test */
    public function it_can_parse_string(): void
    {
        $record = CNAME::parse('www.spatie.be.       300     IN      CNAME   spatie.be.');

        static::assertSame('www.spatie.be', $record->host());
        static::assertSame(300, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('CNAME', $record->type());
        static::assertSame('spatie.be', $record->target());
    }

    /** @test */
    public function it_can_make_from_array(): void
    {
        $record = CNAME::make([
            'host' => 'www.spatie.be',
            'class' => 'IN',
            'ttl' => 300,
            'type' => 'CNAME',
            'target' => 'spatie.be',
        ]);

        static::assertSame('www.spatie.be', $record->host());
        static::assertSame(300, $record->ttl());
        static::assertSame('IN', $record->class());
        static::assertSame('CNAME', $record->type());
        static::assertSame('spatie.be', $record->target());
    }

    /** @test */
    public function it_can_transform_to_string(): void
    {
        $record = CNAME::parse('www.spatie.be.       300     IN      CNAME   spatie.be.');

        static::assertSame("www.spatie.be.\t\t300\tIN\tCNAME\tspatie.be.", strval($record));
    }
}
