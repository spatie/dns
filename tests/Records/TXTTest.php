<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\TXT;

class TXTTest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = TXT::parse('spatie.be.              594     IN      TXT     "v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all"');

        $this->assertSame('spatie.be', $record->host());
        $this->assertSame(594, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('TXT', $record->type());
        $this->assertSame('v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all', $record->txt());
    }

    /** @test */
    public function it_can_parse_long_txt_string()
    {
        $record = TXT::parse('spatie.be.              594     IN      TXT     "v=spf1 a mx ip4:100.101.102.103 ip4:104.105.106.107 ip4:108.109.110.111 ip6:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a include:_spf.google.com include:_spf.c" "reatesend.com ~all"');

        $this->assertSame('v=spf1 a mx ip4:100.101.102.103 ip4:104.105.106.107 ip4:108.109.110.111 ip6:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a:1a1a include:_spf.google.com include:_spf.createsend.com ~all', $record->txt());
    }

    /** @test */
    public function it_can_make_from_array()
    {
        $record = TXT::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 594,
            'type' => 'TXT',
            'txt' => 'v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all',
        ]);

        $this->assertSame('spatie.be', $record->host());
        $this->assertSame(594, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('TXT', $record->type());
        $this->assertSame('v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all', $record->txt());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = TXT::parse('spatie.be.              594     IN      TXT     "v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all"');

        $this->assertSame("spatie.be.\t\t594\tIN\tTXT\t\"v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all\"", strval($record));
    }

    /** @test */
    public function it_can_be_converted_to_an_array()
    {
        $record = TXT::make([
            'host' => 'spatie.be',
            'class' => 'IN',
            'ttl' => 594,
            'type' => 'TXT',
            'txt' => 'v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all',
        ]);

        $data = $record->toArray();
        $this->assertSame('spatie.be', $data['host']);
        $this->assertSame(594, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('TXT', $data['type']);
        $this->assertSame('v=spf1 include:eu.mailgun.org include:spf.factuursturen.be include:sendgrid.net a mx ~all', $data['txt']);
    }

    /** @test */
    public function it_return_null_for_to_few_attributes()
    {
        $record = TXT::parse('spatie.be.              594     IN      TXT');

        $this->assertNull($record);
    }
}
