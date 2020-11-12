<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Spatie\Dns\Records\Record;
use Spatie\Dns\Support\Factory;
use Spatie\Dns\Support\Types;
use Symfony\Component\Process\Process;

class Dig extends Handler
{
    public function __invoke(string $domain, int $flag, string $type): array
    {
        $process = new Process($this->buildCommand($domain, $type));

        $process->enableOutput();

        $process->run();

        if (! $process->isSuccessful()) {
            throw CouldNotFetchDns::digReturnedWithError(trim($process->getErrorOutput()));
        }

        return $this->transform(
            $type,
            $this->parseOutput($process->getOutput())
        );
    }

    public function canHandle(): bool
    {
        $process = new Process(['dig', '-v', '2>&1']);
        $process->run();

        if (stripos($process->getOutput(), 'not found') !== false) {
            return false;
        }

        return $process->isSuccessful();
    }

    protected function buildCommand(string $domain, string $type): array
    {
        return array_filter([
            'dig',
            '+nocmd',
            '+noall',
            '+nomultiline',
            '+answer',
            $this->prepareNameserver(),
            $domain,
            $type,
        ]);
    }

    protected function prepareNameserver(): ?string
    {
        if (empty($this->nameserver)) {
            return null;
        }

        return '@'.$this->nameserver;
    }

    protected function parseOutput(string $output): array
    {
        $output = trim($output);

        if (empty($output)) {
            return [];
        }

        return explode(PHP_EOL, $output);
    }
}
