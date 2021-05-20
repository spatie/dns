<?php

namespace Spatie\Dns\Test\Support;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Support\Domain;

class DomainTest extends TestCase
{
    /** @test */
    public function it_can_get_a_sanitized_version_of_the_domain_name()
    {
        $this->assertEquals('spatie.be', strval(new Domain('spatie.be')));
        $this->assertEquals('www.spatie.be', strval(new Domain('www.spatie.be')));
        $this->assertEquals('spatie.be', strval(new Domain('http://spatie.be')));
        $this->assertEquals('spatie.be', strval(new Domain('https://spatie.be/page')));
        $this->assertEquals('spatie.be', strval(new Domain('https://SPATIE.be')));
        $this->assertEquals('www.spatie.be', strval(new Domain('https://www.SPATIE.be')));
        $this->assertEquals('ftp.spatie.be', strval(new Domain('ftp://ftp.spatie.be')));
        $this->assertEquals('spatie.be', strval(new Domain('freek@spatie.be')));
        $this->assertEquals('spatie.be', strval(new Domain('freek+dns@spatie.be')));
        $this->assertEquals('spatie.be', strval(new Domain('freek.dns@spatie.be')));
    }
}
