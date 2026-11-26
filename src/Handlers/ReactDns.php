<?php

namespace Spatie\Dns\Handlers;

use Composer\InstalledVersions;
use React\Dns\Config\Config;
use React\Dns\Model\Message;
use React\Dns\Model\Record as ResourceRecord;
use React\Dns\Query\Query;
use React\Dns\Query\RetryExecutor;
use React\Dns\Query\SelectiveTransportExecutor;
use React\Dns\Query\TcpTransportExecutor;
use React\Dns\Query\TimeoutException;
use React\Dns\Query\TimeoutExecutor;
use React\Dns\Query\UdpTransportExecutor;
use React\EventLoop\StreamSelectLoop;
use Spatie\Dns\Exceptions\CouldNotFetchDns;
use Throwable;

/**
 * Writes each answer as the line dig prints for it, so the records match the Dig handler.
 */
class ReactDns extends Handler
{
    protected ?int $timeout = 2;

    protected ?int $retries = 2;

    /** @var array<string, int> */
    protected array $typeCodes = [
        'A' => 1,
        'NS' => 2,
        'CNAME' => 5,
        'SOA' => 6,
        'PTR' => 12,
        'MX' => 15,
        'TXT' => 16,
        'AAAA' => 28,
        'SRV' => 33,
        'DS' => 43,
        'DNSKEY' => 48,
        'CAA' => 257,
    ];

    /** @var array<int, string> */
    protected array $responseStatuses = [
        0 => 'NOERROR',
        1 => 'FORMERR',
        2 => 'SERVFAIL',
        3 => 'NXDOMAIN',
        4 => 'NOTIMP',
        5 => 'REFUSED',
        6 => 'YXDOMAIN',
        7 => 'YXRRSET',
        8 => 'NXRRSET',
        9 => 'NOTAUTH',
        10 => 'NOTZONE',
    ];

    /**
     * Response statuses that are real answers. NXDOMAIN means the name does not exist, so
     * returning no records is correct.
     *
     * @var array<int, string>
     */
    protected array $successfulResponseStatuses = ['NOERROR', 'NXDOMAIN'];

    public function __invoke(string $domain, int $flag, string $type): array
    {
        $type = mb_strtoupper($type);

        if (! isset($this->typeCodes[$type])) {
            throw UnsupportedRecordType::make($type);
        }

        $nameservers = $this->nameserverAddresses();

        $description = "{$domain} {$type} @".implode(',', $nameservers);

        // react/dns adds the root dot itself, so a name that already ends in one would end twice.
        $domain = $this->toAscii(rtrim($domain, '.'), $description);

        $response = $this->query(
            new Query($domain, $this->typeCodes[$type], Message::CLASS_IN),
            $nameservers,
            $description,
        );

        $this->guardAgainstErrorResponse($response, $description);

        return $this->transform($type, $this->toLines($response, $type));
    }

    /**
     * Older react/dns versions have no recursion limit in the parser.
     */
    public function canHandle(): bool
    {
        if (! InstalledVersions::isInstalled('react/dns')) {
            return false;
        }

        return version_compare((string) InstalledVersions::getVersion('react/dns'), '1.14.0', '>=');
    }

    /**
     * Like dig, an error status is an answer, so the next nameserver is not asked then.
     *
     * @param  array<int, string>  $nameservers
     */
    protected function query(Query $query, array $nameservers, string $description): Message
    {
        // Like dig, a number of tries below 1 counts as 1. dig spends the extra tries on the
        // first nameserver and asks every other nameserver once.
        $tries = max(1, (int) $this->retries);

        foreach ($nameservers as $nameserver) {
            $result = $this->queryNameserver($query, $nameserver, $tries);

            if ($result instanceof Message) {
                return $result;
            }

            $tries = 1;
        }

        throw CouldNotFetchDns::queryFailed($description, $this->failureReason($result), $result);
    }

    /**
     * A private loop leaves the application's global loop alone. It runs until it is empty,
     * so react/dns can still close the TCP connection of an answer too large for UDP.
     */
    protected function queryNameserver(Query $query, string $nameserver, int $tries): Message|Throwable
    {
        $loop = new StreamSelectLoop;

        // Like dig, a timeout below 1 counts as 1. The tries include the first attempt, and
        // RetryExecutor only counts the attempts after it.
        $executor = new RetryExecutor(
            new TimeoutExecutor(
                new SelectiveTransportExecutor(
                    new UdpTransportExecutor($nameserver, $loop),
                    new TcpTransportExecutor($nameserver, $loop),
                ),
                max(1, (int) $this->timeout),
                $loop,
            ),
            $tries - 1,
        );

        $result = null;

        $settle = function (Message|Throwable $outcome) use (&$result): void {
            $result = $outcome;
        };

        $executor->query($query)->then($settle, $settle);

        $loop->run();

        return $result;
    }

    /**
     * react/dns would send a Unicode name as raw UTF-8, dig sends punycode.
     */
    protected function toAscii(string $domain, string $description): string
    {
        if (mb_check_encoding($domain, 'ASCII')) {
            return $domain;
        }

        if (! function_exists('idn_to_ascii')) {
            throw CouldNotFetchDns::queryFailed($description, 'a Unicode domain name needs the intl extension');
        }

        $ascii = idn_to_ascii($domain, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);

        if ($ascii === false) {
            throw CouldNotFetchDns::queryFailed($description, 'the domain name cannot be converted to punycode');
        }

        return $ascii;
    }

    /**
     * RetryExecutor wraps the last timeout in an exception of its own.
     */
    protected function failureReason(Throwable $error): string
    {
        if ($error->getPrevious() instanceof TimeoutException) {
            return 'no reply from the nameserver';
        }

        return $error->getMessage();
    }

    /** @return array<int, string> */
    protected function nameserverAddresses(): array
    {
        $nameserver = (string) $this->nameserver;

        if ($nameserver === '') {
            return $this->systemNameservers();
        }

        return $this->resolveNameserver($nameserver);
    }

    /** @return array<int, string> */
    protected function systemNameservers(): array
    {
        $nameservers = Config::loadSystemConfigBlocking()->nameservers;

        if ($nameservers !== []) {
            return $nameservers;
        }

        // react/dns finds the Windows nameservers with wmic, which newer Windows versions no
        // longer include. Elsewhere the list is empty when resolv.conf names no nameserver,
        // and dig then sends the query to the local host.
        if (PHP_OS_FAMILY === 'Windows') {
            throw CouldNotFetchDns::noSystemNameserver();
        }

        return ['127.0.0.1'];
    }

    /**
     * react/dns needs an IP address. Like dig, the hostname is looked up on every query.
     *
     * @return array<int, string>
     */
    protected function resolveNameserver(string $nameserver): array
    {
        if (filter_var($nameserver, FILTER_VALIDATE_IP) !== false) {
            return [$nameserver];
        }

        return gethostbynamel($nameserver) ?: throw CouldNotFetchDns::nameserverNotFound($nameserver);
    }

    protected function guardAgainstErrorResponse(Message $response, string $description): void
    {
        if (! $this->throwOnErrorResponse) {
            return;
        }

        $responseStatus = $this->responseStatuses[$response->rcode] ?? "RESERVED{$response->rcode}";

        if (in_array($responseStatus, $this->successfulResponseStatuses, true)) {
            return;
        }

        throw CouldNotFetchDns::nameserverReturnedErrorResponse($responseStatus, $description);
    }

    /**
     * The Dig handler reads the authority section too.
     *
     * @return array<int, string>
     */
    protected function toLines(Message $response, string $type): array
    {
        $lines = [];

        foreach ([...$response->answers, ...$response->authority] as $record) {
            if ($record->type !== $this->typeCodes[$type]) {
                continue;
            }

            $data = $this->formatData($record, $type);

            if ($data === null) {
                continue;
            }

            $lines[] = "{$this->formatName($record->name)}\t{$record->ttl}\tIN\t{$type}\t{$data}";
        }

        return $lines;
    }

    protected function formatData(ResourceRecord $record, string $type): ?string
    {
        $data = $record->data;

        return match ($type) {
            'A', 'AAAA' => $data,
            'CNAME', 'NS', 'PTR' => $this->formatName($data),
            'MX' => "{$data['priority']} {$this->formatName($data['target'])}",
            'SRV' => "{$data['priority']} {$data['weight']} {$data['port']} {$this->formatName($data['target'])}",
            'SOA' => implode(' ', [
                $this->formatName($data['mname']),
                $this->formatName($data['rname']),
                $data['serial'],
                $data['refresh'],
                $data['retry'],
                $data['expire'],
                $data['minimum'],
            ]),
            'TXT' => implode(' ', array_map($this->formatCharacterString(...), $data)),
            'CAA' => "{$data['flag']} {$data['tag']} {$this->formatCharacterString($data['value'])}",
            'DS' => $this->formatDs($data),
            'DNSKEY' => $this->formatDnskey($data),
        };
    }

    protected function formatName(string $name): string
    {
        return rtrim($name, '.').'.';
    }

    /**
     * Escapes the string the way dig does, with a backslash and three digits for any byte
     * that is not printable ASCII.
     */
    protected function formatCharacterString(string $value): string
    {
        $escaped = preg_replace_callback(
            '/[^\x20-\x7E]/',
            fn (array $match): string => sprintf('\\%03d', ord($match[0])),
            addcslashes($value, '"\\'),
        );

        return "\"{$escaped}\"";
    }

    /**
     * react/dns returns DS records undecoded: key tag, algorithm, digest type, digest.
     */
    protected function formatDs(string $data): ?string
    {
        if (strlen($data) < 5) {
            return null;
        }

        $fields = unpack('nkeytag/Calgorithm/Cdigesttype', $data);

        $digest = strtoupper(bin2hex(substr($data, 4)));

        return "{$fields['keytag']} {$fields['algorithm']} {$fields['digesttype']} {$digest}";
    }

    /**
     * react/dns returns DNSKEY records undecoded: flags, protocol, algorithm, public key.
     */
    protected function formatDnskey(string $data): ?string
    {
        if (strlen($data) < 5) {
            return null;
        }

        $fields = unpack('nflags/Cprotocol/Calgorithm', $data);

        $publicKey = base64_encode(substr($data, 4));

        return "{$fields['flags']} {$fields['protocol']} {$fields['algorithm']} {$publicKey}";
    }
}
