<?php

namespace Spatie\Dns\Handlers;

use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Dig extends Handler
{
    protected ?int $timeout = 2;
    protected ?int $retries = 2;

    public function __invoke(string $domain, int $flag, string $type): array
    {
        $command = $this->buildCommand($domain, $type);

        $process = new Process($command);

        $process
            ->enableOutput()
            ->run();

        if (! $process->isSuccessful()) {
            $command = implode(' ', $command);

            throw CouldNotFetchDns::digReturnedWithError($process, $command);
        }

        return $this->transform(
            $type,
            $this->parseOutput($process->getOutput())
        );
    }

    public function canHandle(): bool
    {
        if (! $digPath = (new ExecutableFinder())->find('dig', null, ['/usr/bin'])) {
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
            '+noidnout',
            '+authority',
            '+answer',
            '+nomultiline',
            '+answer',
            $this->prepareRetries(),
            $this->prepareTimeout(),
            $this->prepareNameserver(),
            '-q',
            $domain,
            '-t',
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

    protected function prepareRetries(): ?string
    {
        return "+tries={$this->retries}";
    }

    protected function prepareTimeout(): ?string
    {
        return "+time={$this->timeout}";
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
