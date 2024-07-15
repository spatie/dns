<?php

namespace Spatie\Dns\Records;

use ReflectionClass;
use Spatie\Dns\Exceptions\InvalidArgument;
use Spatie\Dns\Support\Domain;
use Spatie\Macroable\Macroable;
use Stringable;

/**
 * @method string host()
 * @method string ttl()
 * @method string class()
 * @method string type()
 */
abstract class Record implements Stringable
{
    use Macroable {
        __call as protected macroCall;
    }

    protected string $host;
    protected int $ttl;
    protected string $class;
    protected string $type;

    public function __construct(array $attributes)
    {
        $type = $attributes['type'] ?? null;
        $expectedType = (new ReflectionClass($this))->getShortName();

        if ($type !== $expectedType) {
            throw InvalidArgument::wrongRecordType($type, $expectedType);
        }

        foreach ($attributes as $key => $value) {
            $key = str_replace('-', '_', $key);

            if (property_exists($this, $key)) {
                $return = $this->cast($key, $value);
                if(is_array($return)){
                    foreach($return as $key => $subvalue){
                        if(!is_null($subvalue)){
                            $this->$key = $subvalue;
                        }
                    }
                } else {
                    $this->$key = $return;
                }
            }
        }
    }

    /**
     * @param array $record
     *
     * @return static
     */
    public static function make(array $record): self
    {
        return new static($record);
    }

    /**
     * @param string $line
     *
     * @return static
     */
    abstract public static function parse(string $line): ?self;

    abstract public function __toString(): string;

    /**
     * @return array
     */
    abstract public function toArray();

    public function __call(string $name, array $arguments)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return $this->macroCall($name, $arguments);
    }

    protected static function lineToArray(string $line, ?int $limit = null): array
    {
        // Match non-space characters, escaped quotes within quotes, or characters within quotes
        preg_match_all('/(?:\\\\["]|[^"\\s]+|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*")+/u', $line, $matches);
        $parts = $matches[0];

        // If a limit is defined, handle it manually because preg_match_all doesn't support limit
        if ($limit !== null && count($parts) > $limit) {
            $lastPart = implode(' ', array_slice($parts, $limit - 1));
            $parts = array_slice($parts, 0, $limit - 1);
            $parts[] = $lastPart;
        }

        return $parts;
    }

    protected function cast(string $attribute, $value)
    {
        $method = sprintf('cast%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute))));

        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    protected function prepareDomain(string $value): string
    {
        return strval(new Domain(trim($value, '.')));
    }

    protected function prepareInt($value): int
    {
        return intval($value);
    }

    protected function prepareText(string $value): string
    {
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }

        return str_replace('" "', '', $value);
    }

    protected function castHost(string $value): string
    {
        return $this->prepareDomain($value);
    }

    protected function castTtl($value): int
    {
        return $this->prepareInt($value);
    }

    protected function castClass(string $value): string
    {
        return mb_strtoupper($value);
    }

    protected function castType(string $value): string
    {
        return mb_strtoupper($value);
    }

    protected function castV(string $value): ?object
    {
        preg_match('/v=([a-zA-Z0-9]+);?\W(.*)/', $value, $matches);
        if (empty($matches)) {
            return null;
        }
        $v = "Spatie\\Dns\\TXTRecords\\".mb_strtoupper($matches[1]);
        return new $v($matches[2]);
    }
}
