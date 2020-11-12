<?php

namespace Spatie\Dns\Records;

use BadMethodCallException;

/**
 * @method string host()
 * @method string ttl()
 * @method string class()
 * @method string type()
 */
abstract class Record
{
    protected string $host;
    protected int $ttl;
    protected string $class;
    protected string $type;

    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $key = str_replace('-', '_', $key);

            if (property_exists($this, $key)) {
                $this->$key = $value;
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
    abstract public static function parse(string $line): self;

    abstract public function __toString(): string;

    public function __call(string $name, array $arguments)
    {
        if(property_exists($this, $name)) {
            return $this->$name;
        }

        throw new BadMethodCallException();
    }

    protected static function lineToArray(string $line, ?int $limit = null): array
    {
        return explode(
            ' ',
            preg_replace('/\s+/', ' ', $line),
            $limit
        );
    }
}
