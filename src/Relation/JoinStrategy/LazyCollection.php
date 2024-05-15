<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TSource
 * @template TKey
 * @implements LazyCollectionInterface<TSource>
 */
class LazyCollection implements LazyCollectionInterface
{
    /**
     * @var ?TSource[]
     */
    private ?array $source = null;

    /**
     * @var ?TKey
     */
    private mixed $key;

    /**
     * @var ?callable(TKey):TSource[]
     */
    private $evaluator;

    /**
     * @param TKey $key
     * @param callable(TKey):TSource[] $evaluator
     */
    public function __construct($key, callable $evaluator)
    {
        $this->key = $key;
        $this->evaluator = $evaluator;
    }

    /**
     * @psalm-assert !null $this->source
     * @return TSource[]
     */
    public function get(): array
    {
        if ($this->evaluator !== null) {
            /** @var TKey $this->key */
            $this->source = ($this->evaluator)($this->key);
            $this->key = null;
            $this->evaluator = null;
        }
        /** @var TSource[] $this->source */
        return $this->source;
    }

    public function offsetExists(mixed $offset): bool
    {
        $source = $this->get();
        return isset($source[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $source = $this->get();
        return $source[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->get();
        if ($offset !== null) {
            $this->source[$offset] = $value;
        } else {
            $this->source[] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->get();
        unset($this->source[$offset]);
    }

    public function getIterator(): \Traversable
    {
        $source = $this->get();
        return new \ArrayIterator($source);
    }

    public function count(): int
    {
        $source = $this->get();
        return count($source);
    }

    public function serialize(): string
    {
        $source = $this->get();
        return serialize($source);
    }

    public function unserialize(string $data): void
    {
        $this->source = unserialize($data);
    }
}
