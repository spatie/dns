<?php

namespace Spatie\DnsInfo\Test;

use PHPUnit\Framework\TestCase;
use Spatie\DnsInfo\DnsInfo;

class DnsInfoTest extends TestCase
{
    protected $dns;

    protected function setUp()
    {
        parent::setUp();

        $this->dns = new DnsInfo('spatie.be');
    }

    /** @test */
    public function it_accepts_domain_name_with_class_init()
    {
        $this->assertObjectHasAttribute('domain', $this->dns);
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function it_throws_an_exception_if_an_empty_string_is_passed()
    {
        $dns = new DnsInfo('');
    }

    /** @test */
    public function it_sanatizes_the_given_domain_name()
    {
        $dns = new DnsInfo('https://spatie.be');

        $this->assertEquals('spatie.be', $dns->getDomain());

        $dns = new DnsInfo('http://spatie.be/');

        $this->assertEquals('spatie.be', $dns->getDomain());
    }

    /** @test */
    public function it_fetches_all_dns_records_for_the_given_domain_name()
    {
        $this->dns->getAllRecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_a_type_records_for_the_given_domain_name()
    {
        $this->dns->getARecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_aaaa_type_records_for_the_given_domain_name()
    {
        $this->dns->getAAAARecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_ns_type_records_for_the_given_domain_name()
    {
        $this->dns->getNSRecords();

        $this->assertTrue(true);
    }

     /** @test */
    public function it_fetches_soa_type_records_for_the_given_domain_name()
    {
        $this->dns->getSOARecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_mx_type_records_for_the_given_domain_name()
    {
        $this->dns->getMXRecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_txt_type_records_for_the_given_domain_name()
    {
        $this->dns->getTXTRecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_dnskey_type_records_for_the_given_domain_name()
    {
        $this->dns->getDNSKEYRecords();

        $this->assertTrue(true);
    }
}
