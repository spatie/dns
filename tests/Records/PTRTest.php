<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\PTR;

class PTRTest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = PTR::parse('1.73.1.5.in-addr.arpa.              3600     IN      PTR       ae0.452.fra.as205948.creoline.net.');

        $this->assertSame('1.73.1.5.in-addr.arpa', $record->reversDnsName());
        $this->assertSame(3600, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('PTR', $record->type());
        $this->assertSame('ae0.452.fra.as205948.creoline.net.', $record->name());
    }

    /** @test */
    public function it_can_make_from_array()
    {
        $record = PTR::make([
            'reversDnsName' => '1.73.1.5.in-addr.arpa.',
            'class' => 'IN',
            'ttl' => 3600,
            'type' => 'PTR',
            'name' => 'ae0.452.fra.as205948.creoline.net.',
        ]);

        $this->assertSame('1.73.1.5.in-addr.arpa', $record->reversDnsName());
        $this->assertSame(3600, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('PTR', $record->type());
        $this->assertSame('ae0.452.fra.as205948.creoline.net.', $record->name());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = PTR::parse('1.73.1.5.in-addr.arpa.              3600     IN      PTR       ae0.452.fra.as205948.creoline.net.');

        $this->assertSame("1.73.1.5.in-addr.arpa.\t\t3600\tIN\tPTR\tae0.452.fra.as205948.creoline.net.", strval($record));
    }

    /** @test */
    public function it_can_be_converted_to_an_array()
    {
        $record = PTR::make([
            'reversDnsName' => '1.73.1.5.in-addr.arpa.',
            'class' => 'IN',
            'ttl' => 3600,
            'type' => 'PTR',
            'name' => 'ae0.452.fra.as205948.creoline.net.',
        ]);

        $data = $record->toArray();
        $this->assertSame('1.73.1.5.in-addr.arpa', $data['reversDnsName']);
        $this->assertSame(3600, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('PTR', $data['type']);
        $this->assertSame('ae0.452.fra.as205948.creoline.net.', $data['name']);
    }

    /** @test */
    public function it_return_null_for_to_few_attributes()
    {
        $record = PTR::parse('1.73.1.5.in-addr.arpa.              3600     IN      PTR');

        $this->assertNull($record);
    }
}
