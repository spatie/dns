<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\MX;

class MXTest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = MX::parse('spatie.be.              1665    IN      MX      10 ASPMX.L.GOOGLE.COM.');

        $this->assertSame('spatie.be', $record->host());
        $this->assertSame(1665, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('MX', $record->type());
        $this->assertSame(10, $record->pri());
        $this->assertSame('aspmx.l.google.com', $record->target());
    }

    /** @test */
    public function it_can_make_from_array()
    {
        $record = MX::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 1665,
            'type' => 'MX',
            'pri' => 10,
            'target' => 'ASPMX.L.GOOGLE.COM',
        ]);

        $this->assertSame('spatie.be', $record->host());
        $this->assertSame(1665, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('MX', $record->type());
        $this->assertSame(10, $record->pri());
        $this->assertSame('aspmx.l.google.com', $record->target());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = MX::parse('spatie.be.              1665    IN      MX      10 ASPMX.L.GOOGLE.COM.');

        $this->assertSame("spatie.be.\t\t1665\tIN\tMX\t10\taspmx.l.google.com.", strval($record));
    }
}
