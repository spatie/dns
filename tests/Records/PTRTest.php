<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\PTR;

class PTRTest extends TestCase
{
    public function rDnsProvider()
    {
        return [
            ['1.73.1.5.in-addr.arpa.', '1.73.1.5.in-addr.arpa'],
            ['1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.1.0.0.0.0.c.f.6.7.0.a.2.ip6.arpa.', '1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.1.0.0.0.0.c.f.6.7.0.a.2.ip6.arpa'],
        ];
    }

    /** @test @dataProvider rDnsProvider */
    public function it_can_parse_string($rDNS, $trimmedRDNS)
    {
        $record = PTR::parse($rDNS . '              3600     IN      PTR       ae0.452.fra.as205948.creoline.net.');

        $this->assertSame($trimmedRDNS, $record->reversDnsName());
        $this->assertSame(3600, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('PTR', $record->type());
        $this->assertSame('ae0.452.fra.as205948.creoline.net.', $record->name());
    }

    /** @test @dataProvider rDnsProvider */
    public function it_can_make_from_array($rDNS, $trimmedRDNS)
    {
        $record = PTR::make([
            'reversDnsName' => $rDNS,
            'class' => 'IN',
            'ttl' => 3600,
            'type' => 'PTR',
            'name' => 'ae0.452.fra.as205948.creoline.net.',
        ]);

        $this->assertSame($trimmedRDNS, $record->reversDnsName());
        $this->assertSame(3600, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('PTR', $record->type());
        $this->assertSame('ae0.452.fra.as205948.creoline.net.', $record->name());
    }

    /** @test @dataProvider rDnsProvider */
    public function it_can_transform_to_string($rDNS)
    {
        $record = PTR::parse($rDNS. '              3600     IN      PTR       ae0.452.fra.as205948.creoline.net.');

        $this->assertSame($rDNS . "\t\t3600\tIN\tPTR\tae0.452.fra.as205948.creoline.net.", strval($record));
    }

    /** @test @dataProvider rDnsProvider */
    public function it_can_be_converted_to_an_array($rDNS, $trimmedRDNS)
    {
        $record = PTR::make([
            'reversDnsName' => $rDNS,
            'class' => 'IN',
            'ttl' => 3600,
            'type' => 'PTR',
            'name' => 'ae0.452.fra.as205948.creoline.net.',
        ]);

        $data = $record->toArray();
        $this->assertSame($trimmedRDNS, $data['reversDnsName']);
        $this->assertSame(3600, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('PTR', $data['type']);
        $this->assertSame('ae0.452.fra.as205948.creoline.net.', $data['name']);
    }

    /** @test @dataProvider rDnsProvider */
    public function it_return_null_for_to_few_attributes($rDNS)
    {
        $record = PTR::parse($rDNS . '              3600     IN      PTR');

        $this->assertNull($record);
    }
}
