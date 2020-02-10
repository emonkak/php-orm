<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ObjectResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T
 */
class ObjectFetcher implements FetcherInterface
{
    /**
     * @var class-string<T>
     */
    private $class;

    /**
     * @var ?mixed[]
     */
    private $constructorArguments;

    /**
     * @param class-string<T> $class
     */
    public function __construct($class, array $constructorArguments = null)
    {
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function fetch(PDOStatementInterface $stmt): ResultSetInterface
    {
        return new ObjectResultSet($stmt, $this->class, $this->constructorArguments);
    }
}
