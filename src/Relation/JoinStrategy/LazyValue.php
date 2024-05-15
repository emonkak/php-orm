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
     * @var ?TValue
     */
    private mixed $value = null;

    /**
     * @var ?TKey
     */
    private mixed $key;

    /**
     * @var ?callable(TKey):TValue
     */
    private $evaluator;

    /**
     * @param TKey $key
     * @param callable(TKey):TValue $evaluator
     */
    public function __construct(mixed $key, callable $evaluator)
    {
        $this->key = $key;
        $this->evaluator = $evaluator;
    }

    /**
     * @return TValue
     */
    public function get(): mixed
    {
        if ($this->evaluator !== null) {
            /** @var TKey $this->key */
            $this->value = ($this->evaluator)($this->key);
            $this->key = null;
            $this->evaluator = null;
        }
        /** @var TValue $this->value */
        return $this->value;
    }

    public function serialize(): string
    {
        $value = $this->get();
        return serialize($value);
    }

    public function unserialize(string $data): void
    {
        $this->value = unserialize($data);
    }
}
