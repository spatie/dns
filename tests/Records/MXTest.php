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

    /** @test */
    public function it_can_be_converted_to_an_array()
    {
        $record = MX::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 1665,
            'type' => 'MX',
            'pri' => 10,
            'target' => 'ASPMX.L.GOOGLE.COM',
        ]);

        $data = $record->toArray();
        $this->assertSame('spatie.be', $data['host']);
        $this->assertSame(1665, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('MX', $data['type']);
        $this->assertSame(10, $data['pri']);
        $this->assertSame('aspmx.l.google.com', $data['target']);
    }

    /** @test */
    public function it_return_null_for_to_few_attributes()
    {
        $record = MX::parse('spatie.be.              1665    IN      MX      10');

        $this->assertNull($record);
    }
}
