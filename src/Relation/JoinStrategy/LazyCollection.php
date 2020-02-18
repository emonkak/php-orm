<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TSource
 * @template TKey
 * @implements \IteratorAggregate<TSource>
 * @implements \ArrayAccess<array-key,TSource>
 */
class LazyCollection implements \ArrayAccess, \Countable, \IteratorAggregate, \Serializable
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
     * @psalm-return TSource[]
     * @psalm-assert !null $this->source
     */
    public function get(): array
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

    public function offsetExists($offset)
    {
        $source = $this->get();
        return isset($source[$offset]);
    }

    public function offsetGet($offset)
    {
        $source = $this->get();
        return $source[$offset];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->get();
        return $this->source[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->get();
        unset($this->source[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        $source = $this->get();
        return new \ArrayIterator($source);
    }

    public function count()
    {
        $source = $this->get();
        return count($source);
    }

    public function serialize()
    {
        $source = $this->get();
        return serialize($source);
    }

    public function unserialize($data)
    {
        $this->source = unserialize($data);
    }
}
