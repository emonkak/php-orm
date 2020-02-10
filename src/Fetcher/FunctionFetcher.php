<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\FunctionResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class FunctionFetcher implements FetcherInterface
{
    /**
     * @var callable(array):mixed
     */
    private $instantiator;

    /**
     * @var class-string
     */
    private $class;

    /**
     * @param class-string $class
     * @return self
     */
    public static function ofConstructor(string $class): self
    {
        $instantiator = \Closure::bind(
            static function($props) use ($class) {
                return new $class($props);
            },
            null,
            $class
        );
        return new FunctionFetcher($instantiator, $class);
    }

    /**
     * @param callable(array):mixed $instantiator
     * @param class-string $class
     */
    public function __construct(callable $instantiator, string $class)
    {
        $this->instantiator = $instantiator;
        $this->class = $class;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function fetch(PDOStatementInterface $stmt): ResultSetInterface
    {
        return new FunctionResultSet($stmt, $this->instantiator, $this->class);
    }
}
