<?php

namespace Spatie\Dns\Handlers;

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
 * Queries the nameserver from inside PHP with react/dns, so no dig process is started.
 * Each answer is written out as the line dig would print and parsed the same way, so the
 * records are equal to the ones the Dig handler returns.
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

    /**
     * The names dig prints for each response code. Codes 11 to 15 have no name, and dig
     * prints them as RESERVED11 to RESERVED15.
     *
     * @var array<int, string>
     */
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

    /**
     * Holds the IP addresses of each nameserver this handler has used, so a hostname is only
     * looked up once. The empty key holds the system nameservers.
     *
     * @var array<string, array<int, string>>
     */
    protected array $resolvedNameservers = [];

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

    public function canHandle(): bool
    {
        return class_exists(UdpTransportExecutor::class);
    }

    /**
     * Asks each nameserver in turn until one answers, the way dig tries each system
     * nameserver. An error status is an answer too, so like dig it does not move on then.
     *
     * @param  array<int, string>  $nameservers
     */
    protected function query(Query $query, array $nameservers, string $description): Message
    {
        foreach ($nameservers as $nameserver) {
            $result = $this->queryNameserver($query, $nameserver);

            if ($result instanceof Message) {
                return $result;
            }
        }

        throw CouldNotFetchDns::queryFailed($description, $this->failureReason($result), $result);
    }

    /**
     * Runs a private event loop until the query has an answer or has failed. A private loop
     * cannot start or stop timers that the application put on the global loop.
     *
     * The loop is not stopped when the answer arrives. It runs until it is empty, so react/dns
     * can still close the TCP connection it opens for an answer that was too large for UDP.
     */
    protected function queryNameserver(Query $query, string $nameserver): Message|Throwable
    {
        $loop = new StreamSelectLoop;

        // Like dig, a timeout or a number of tries below 1 counts as 1. The tries include the
        // first attempt, and RetryExecutor only counts the attempts after it.
        $executor = new RetryExecutor(
            new TimeoutExecutor(
                new SelectiveTransportExecutor(
                    new UdpTransportExecutor($nameserver, $loop),
                    new TcpTransportExecutor($nameserver, $loop),
                ),
                max(1, (int) $this->timeout),
                $loop,
            ),
            max(1, (int) $this->retries) - 1,
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
     * Converts a Unicode domain name to its punycode form, the way dig does before it sends
     * the query. react/dns would send the UTF-8 bytes as they are.
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
     * The retry executor wraps the last timeout in an exception of its own, whose message
     * repeats the query. The timeout is the actual reason.
     */
    protected function failureReason(Throwable $error): string
    {
        if ($error->getPrevious() instanceof TimeoutException) {
            return 'no reply from the nameserver';
        }

        return $error->getMessage();
    }

    /**
     * Returns the IP addresses to send the query to, in order. dig accepts a hostname, but
     * react/dns needs an IP address.
     *
     * @return array<int, string>
     */
    protected function nameserverAddresses(): array
    {
        $nameserver = (string) $this->nameserver;

        return $this->resolvedNameservers[$nameserver] ??= $this->resolveNameserver($nameserver);
    }

    /**
     * @return array<int, string>
     */
    protected function resolveNameserver(string $nameserver): array
    {
        if ($nameserver === '') {
            return Config::loadSystemConfigBlocking()->nameservers ?: throw CouldNotFetchDns::noSystemNameserver();
        }

        if (filter_var($nameserver, FILTER_VALIDATE_IP) !== false) {
            return [$nameserver];
        }

        $address = gethostbyname($nameserver);

        if (filter_var($address, FILTER_VALIDATE_IP) === false) {
            throw CouldNotFetchDns::nameserverNotFound($nameserver);
        }

        return [$address];
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
     * Writes each record of the requested type as the line dig prints for it. The Dig
     * handler reads both the answer and the authority section, so this does too.
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

    /**
     * react/dns leaves the trailing dot of the root label off, dig prints it.
     */
    protected function formatName(string $name): string
    {
        return rtrim($name, '.').'.';
    }

    /**
     * Quotes the string the way dig does. A quote or a backslash gets a backslash in front,
     * and a byte that is not printable ASCII is written as a backslash and three digits.
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
     * react/dns does not decode DS records and returns the raw bytes. They hold a 2 byte key
     * tag, a 1 byte algorithm, a 1 byte digest type and the digest.
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
     * react/dns does not decode DNSKEY records and returns the raw bytes. They hold 2 bytes
     * of flags, a 1 byte protocol, a 1 byte algorithm and the public key.
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
