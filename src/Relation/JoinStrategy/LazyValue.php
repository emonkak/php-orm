<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TValue
 * @template TKey
 * @implements LazyValueInterface<TValue>
 */
class LazyValue implements LazyValueInterface
{
    /**
     * @psalm-var ?TValue
     * @var mixed
     */
    private $value = null;

    /**
     * @psalm-var ?TKey
     * @var mixed
     */
    private $key;

    /**
     * @psalm-var ?callable(TKey):TValue
     * @var ?callable
     */
    private $evaluator;

    /**
     * @psalm-param TKey $key
     * @psalm-param callable(TKey):TValue $evaluator
     */
    public function __construct($key, callable $evaluator)
    {
        $this->key = $key;
        $this->evaluator = $evaluator;
    }

    /**
     * @psalm-return TValue
     */
    public function get()
    {
        if ($this->evaluator !== null) {
            /** @psalm-var TKey $this->key */
            $this->value = ($this->evaluator)($this->key);
            $this->key = null;
            $this->evaluator = null;
        }
        /** @psalm-var TValue $this->value */
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $value = $this->get();
        return serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        $this->value = unserialize($data);
    }
}
