<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

class Model
{
    /**
     * @var array<string,mixed>
     */
    private array $props = [];

    /**
     * @param array<string,mixed> $props
     */
    public function __construct(array $props)
    {
        $this->props = $props;
    }

    public function __get(mixed $key): mixed
    {
        return $this->props[$key];
    }

    public function __set(mixed $key, mixed $value): void
    {
        $this->props[$key] = $value;
    }

    public function __unset(mixed $key): void
    {
        unset($this->props[$key]);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->props;
    }
}
