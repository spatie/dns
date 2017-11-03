<?php

namespace Spatie\Dns\Test;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Dns;

class DnsTest extends TestCase
{
    protected $dns;

    protected function setUp()
    {
        parent::setUp();

        $this->dns = new Dns('spatie.be');
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
        $dns = new Dns('');
    }

    /** @test */
    public function it_sanatizes_the_given_domain_name()
    {
        $dns = new Dns('https://spatie.be');

        $this->assertEquals('spatie.be', $dns->getDomain());

        $dns = new Dns('http://spatie.be/');

        $this->assertEquals('spatie.be', $dns->getDomain());
    }

    /** @test */
    public function it_fetches_all_dns_records_for_the_given_domain_name()
    {
        $this->dns->getAllRecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_records_for_a_given_single_record_type()
    {
        $this->dns->getRecords('txt');
        $this->dns->getRecords('TXT');

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_records_for_a_given_array_of_record_types()
    {
        $this->dns->getRecords(['A', 'TXT']);

        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function it_throws_an_exception_if_an_invalid_record_type_is_passed()
    {
        $this->dns->getRecords('xyz');
        $this->dns->getRecords(['A', 'XYZ']);
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
        $this->dns->getAaaaRecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_ns_type_records_for_the_given_domain_name()
    {
        $this->dns->getNsRecords();

        $this->assertTrue(true);
    }

     /** @test */
    public function it_fetches_soa_type_records_for_the_given_domain_name()
    {
        $this->dns->getSoaRecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_mx_type_records_for_the_given_domain_name()
    {
        $this->dns->getMxRecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_txt_type_records_for_the_given_domain_name()
    {
        $this->dns->getTxtRecords();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_fetches_dnskey_type_records_for_the_given_domain_name()
    {
        $this->dns->getDnsKeyRecords();

        $this->assertTrue(true);
    }
}
