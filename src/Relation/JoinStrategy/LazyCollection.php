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
     * {@inheritDoc}
     * @psalm-assert !null $this->source
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
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        $source = $this->get();
        return isset($source[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        $source = $this->get();
        return $source[$offset];
    }

    /**
     * {@inheritDoc}
     * @psalm-param array-key|null $offset
     */
    public function offsetSet($offset, $value)
    {
        $this->get();
        if ($offset !== null) {
            $this->source[$offset] = $value;
        } else {
            $this->source[] = $value;
        }
        return $value;
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $source = $this->get();
        return count($source);
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        $source = $this->get();
        return serialize($source);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $this->source = unserialize($data);
    }
}
