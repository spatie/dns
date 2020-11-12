<?php

namespace Spatie\Dns\Test\Support;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Support\Domain;
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
    public function it_can_transform_flag_to_name(): void
    {
        static::assertEquals(['A'], $this->types->toNames(DNS_A));
        static::assertEquals(['AAAA'], $this->types->toNames(DNS_AAAA));
        static::assertEquals(['CAA'], $this->types->toNames(DNS_CAA));
        static::assertEquals(['CNAME'], $this->types->toNames(DNS_CNAME));
        static::assertEquals(['MX'], $this->types->toNames(DNS_MX));
        static::assertEquals(['NS'], $this->types->toNames(DNS_NS));
        static::assertEquals(['SOA'], $this->types->toNames(DNS_SOA));
        static::assertEquals(['SRV'], $this->types->toNames(DNS_SRV));
        static::assertEquals(['TXT'], $this->types->toNames(DNS_TXT));
    }

    /** @test */
    public function it_can_transform_flags_to_names(): void
    {
        static::assertEquals(['A', 'AAAA'], $this->types->toNames(DNS_A | DNS_AAAA));
        static::assertEquals(['NS', 'SOA'], $this->types->toNames(DNS_NS | DNS_SOA));
    }

    /** @test */
    public function it_can_transform_name_to_flag(): void
    {
        static::assertEquals(DNS_A, $this->types->toFlags(['A']));
        static::assertEquals(DNS_AAAA, $this->types->toFlags(['AAAA']));
        static::assertEquals(DNS_CAA, $this->types->toFlags(['CAA']));
        static::assertEquals(DNS_CNAME, $this->types->toFlags(['CNAME']));
        static::assertEquals(DNS_MX, $this->types->toFlags(['MX']));
        static::assertEquals(DNS_NS, $this->types->toFlags(['NS']));
        static::assertEquals(DNS_SOA, $this->types->toFlags(['SOA']));
        static::assertEquals(DNS_SRV, $this->types->toFlags(['SRV']));
        static::assertEquals(DNS_TXT, $this->types->toFlags(['TXT']));
    }

    /** @test */
    public function it_can_transform_names_to_flags(): void
    {
        static::assertEquals(DNS_A | DNS_AAAA, $this->types->toFlags(['A', 'AAAA']));
        static::assertEquals(DNS_NS | DNS_SOA, $this->types->toFlags(['NS', 'SOA']));
        static::assertEquals(DNS_A | DNS_AAAA | DNS_CNAME, $this->types->toFlags(['A', 'aaAA', 'cname']));
    }
}
