<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Dig extends Handler
{
    public function __invoke(string $domain, int $flag, string $type): array
    {
        $process = new Process($this->buildCommand($domain, $type));

        $process
            ->enableOutput()
            ->run();

        if (! $process->isSuccessful()) {
            throw CouldNotFetchDns::digReturnedWithError($process);
        }

        return $this->transform(
            $type,
            $this->parseOutput($process->getOutput())
        );
    }

    public function canHandle(): bool
    {
        if (! $digPath = (new ExecutableFinder())->find('dig')) {
            return false;
        }

        $process = new Process([$digPath, '-v', '2>&1']);
        $process->run();

        if (str_contains($process->getOutput(), 'not found')) {
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

        return "@{$this->nameserver}";
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
