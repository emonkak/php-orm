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
     * @psalm-var ?TSource[]
     * @var mixed
     */
    private $source = null;

    /**
     * @psalm-var ?TKey
     * @var mixed
     */
    private $key;

    /**
     * @psalm-var ?callable(TKey):TSource[]
     * @var ?callable
     */
    private $evaluator;

    /**
     * @psalm-param TKey $key
     * @psalm-param callable(TKey):TSource[] $evaluator
     */
    public function __construct($key, callable $evaluator)
    {
        $this->key = $key;
        $this->evaluator = $evaluator;
    }

    /**
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @psalm-assert !null $this->source
     * @psalm-return TSource[]
     */
    public function get()
    {
        if ($this->evaluator !== null) {
            /** @psalm-var TKey $this->key */
            $this->source = ($this->evaluator)($this->key);
            $this->key = null;
            $this->evaluator = null;
        }
        /** @psalm-var TSource[] $this->source */
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        $source = $this->get();
        return isset($source[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        $source = $this->get();
        return $source[$offset];
    }

    /**
     * {@inheritdoc}
     * @psalm-param array-key|null $offset
     */
    public function offsetSet(mixed $offset, $value): void
    {
        $this->get();
        if ($offset !== null) {
            $this->source[$offset] = $value;
        } else {
            $this->source[] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->get();
        unset($this->source[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        $source = $this->get();
        return new \ArrayIterator($source);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $source = $this->get();
        return count($source);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $source = $this->get();
        return serialize($source);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        $this->source = unserialize($data);
    }
}
