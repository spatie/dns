<?php

use Spatie\Dns\Records\A;
use Spatie\Dns\Records\Record;

it('is macroable', function () {
    Record::macro('ping', fn () => 'pong');

    $record = A::parse('spatie.be.              900     IN      A       138.197.187.74');

    expect($record->ping())->toBe('pong');
});
