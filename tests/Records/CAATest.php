<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\CAA;

class CAATest extends TestCase
{
    /** @test */
    public function it_can_parse_string()
    {
        $record = CAA::parse('google.com.             86400   IN      CAA     0 issue "pki.goog"');

        $this->assertSame('google.com', $record->host());
        $this->assertSame(86400, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('CAA', $record->type());
        $this->assertSame(0, $record->flags());
        $this->assertSame('issue', $record->tag());
        $this->assertSame('pki.goog', $record->value());
    }

    /** @test */
    public function it_can_make_from_array()
    {
        $record = CAA::make([
            'host' => 'google.com',
            'class' => 'IN',
            'ttl' => 86400,
            'type' => 'CAA',
            'flags' => 0,
            'tag' => 'issue',
            'value' => 'pki.goog',
        ]);

        $this->assertSame('google.com', $record->host());
        $this->assertSame(86400, $record->ttl());
        $this->assertSame('IN', $record->class());
        $this->assertSame('CAA', $record->type());
        $this->assertSame(0, $record->flags());
        $this->assertSame('issue', $record->tag());
        $this->assertSame('pki.goog', $record->value());
    }

    /** @test */
    public function it_can_transform_to_string()
    {
        $record = CAA::parse('google.com.             86400   IN      CAA     0 issue "pki.goog"');

        $this->assertSame("google.com.\t\t86400\tIN\tCAA\t0\tissue\t\"pki.goog\"", strval($record));
    }

    /** @test */
    public function it_can_be_converted_to_an_array()
    {
        $record = CAA::make([
            'host' => 'google.com',
            'class' => 'IN',
            'ttl' => 86400,
            'type' => 'CAA',
            'flags' => 0,
            'tag' => 'issue',
            'value' => 'pki.goog',
        ]);

        $data = $record->toArray();
        $this->assertSame('google.com', $data['host']);
        $this->assertSame(86400, $data['ttl']);
        $this->assertSame('IN', $data['class']);
        $this->assertSame('CAA', $data['type']);
        $this->assertSame(0, $data['flags']);
        $this->assertSame('issue', $data['tag']);
        $this->assertSame('pki.goog', $data['value']);
    }
}
