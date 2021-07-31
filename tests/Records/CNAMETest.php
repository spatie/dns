<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\CNAME;

class CNAMETest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = CNAME::parse('www.spatie.be.       300     IN      CNAME   spatie.be.');

        $this->assertSame('www.spatie.be', $record->host());
        $this->assertSame(300, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('CNAME', $record->type());
        $this->assertSame('spatie.be', $record->target());
    }

    /** @test */
    public function it_can_make_from_array()
    {
        $record = CNAME::make([
            'host' => 'www.spatie.be',
            'class' => 'IN',
            'ttl' => 300,
            'type' => 'CNAME',
            'target' => 'spatie.be',
        ]);

        $this->assertSame('www.spatie.be', $record->host());
        $this->assertSame(300, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('CNAME', $record->type());
        $this->assertSame('spatie.be', $record->target());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = CNAME::parse('www.spatie.be.       300     IN      CNAME   spatie.be.');

        $this->assertSame("www.spatie.be.\t\t300\tIN\tCNAME\tspatie.be.", strval($record));
    }

    /** @test */
    public function it_can_be_converted_to_an_array()
    {
        $record = CNAME::make([
            'host' => 'www.spatie.be',
            'class' => 'IN',
            'ttl' => 300,
            'type' => 'CNAME',
            'target' => 'spatie.be',
        ]);

        $data = $record->toArray();
        $this->assertSame('www.spatie.be', $data['host']);
        $this->assertSame(300, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('CNAME', $data['type']);
        $this->assertSame('spatie.be', $data['target']);
    }
}
