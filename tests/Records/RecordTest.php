<?php

namespace Spatie\Dns\Test\Records;

use PHPUnit\Framework\TestCase;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\Record;

class RecordTest extends TestCase
{
    /** @test */
    public function a_record_is_macroable()
    {
        Record::macro('ping', fn() => 'pong');

        $record = A::parse('spatie.be.              900     IN      A       138.197.187.74');

        $this->assertEquals('pong', $record->ping());
    }
}
