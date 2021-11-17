<?php

namespace Spatie\Dns\Test\TestClasses;

use Spatie\Dns\Handlers\Handler;
use Spatie\Dns\Support\Factory;

class CustomHandler extends Handler
{
    public function __construct()
    {
        // override default constructor
    }

    public function __invoke(string $domain, int $flag, string $type): array
    {
        return ["custom-handler-results-{$type}"];
    }

    public function canHandle(): bool
    {
        return true;
    }
}
