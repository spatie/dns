<?php

namespace Spatie\Dns\Test\Support;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Support\Types;

class TypesTest extends TestCase
{
    protected Types $types;

    protected function setUp(): void
    {
        parent::setUp();

        $this->types = new Types();
    }

    /** @test */
    public function it_can_transform_flag_to_name()
    {
        $this->assertSame([DNS_A => 'A'], $this->types->toNames(DNS_A));
        $this->assertSame([DNS_AAAA => 'AAAA'], $this->types->toNames(DNS_AAAA));
        if (defined('DNS_CAA')) {
            $this->assertSame([DNS_CAA => 'CAA'], $this->types->toNames(DNS_CAA));
        }
        $this->assertSame([DNS_CNAME => 'CNAME'], $this->types->toNames(DNS_CNAME));
        $this->assertSame([DNS_MX => 'MX'], $this->types->toNames(DNS_MX));
        $this->assertSame([DNS_NS => 'NS'], $this->types->toNames(DNS_NS));
        $this->assertSame([DNS_SOA => 'SOA'], $this->types->toNames(DNS_SOA));
        $this->assertSame([DNS_SRV => 'SRV'], $this->types->toNames(DNS_SRV));
        $this->assertSame([DNS_TXT => 'TXT'], $this->types->toNames(DNS_TXT));
    }

    /** @test */
    public function it_can_transform_flags_to_names()
    {
        $this->assertSame([DNS_A => 'A', DNS_AAAA => 'AAAA'], $this->types->toNames(DNS_A | DNS_AAAA));
        $this->assertSame([DNS_NS => 'NS', DNS_SOA => 'SOA'], $this->types->toNames(DNS_NS | DNS_SOA));
    }

    /** @test */
    public function it_can_transform_name_to_flag()
    {
        $this->assertSame(DNS_A, $this->types->toFlags(['A']));
        $this->assertSame(DNS_AAAA, $this->types->toFlags(['AAAA']));
        if (defined('DNS_CAA')) {
            $this->assertSame(DNS_CAA, $this->types->toFlags(['CAA']));   
        }
        $this->assertSame(DNS_CNAME, $this->types->toFlags(['CNAME']));
        $this->assertSame(DNS_MX, $this->types->toFlags(['MX']));
        $this->assertSame(DNS_NS, $this->types->toFlags(['NS']));
        $this->assertSame(DNS_SOA, $this->types->toFlags(['SOA']));
        $this->assertSame(DNS_SRV, $this->types->toFlags(['SRV']));
        $this->assertSame(DNS_TXT, $this->types->toFlags(['TXT']));
    }

    /** @test */
    public function it_can_transform_names_to_flags()
    {
        $this->assertSame(DNS_A | DNS_AAAA, $this->types->toFlags(['A', 'AAAA']));
        $this->assertSame(DNS_NS | DNS_SOA, $this->types->toFlags(['NS', 'SOA']));
        $this->assertSame(DNS_A | DNS_AAAA | DNS_CNAME, $this->types->toFlags(['A', 'aaAA', 'cname']));
    }
}
