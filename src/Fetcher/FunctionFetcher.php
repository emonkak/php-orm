<?php

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\FunctionResultSet;

class FunctionFetcher implements FetcherInterface
{
    /**
     * @var callable
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
    public static function ofConstructor($class)
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
     * @param callable     $instantiator
     * @param class-string $class
     */
    public function __construct(callable $instantiator, $class)
    {
        $this->instantiator = $instantiator;
        $this->class = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(PDOStatementInterface $stmt)
    {
        return new FunctionResultSet($stmt, $this->instantiator, $this->class);
    }
}
