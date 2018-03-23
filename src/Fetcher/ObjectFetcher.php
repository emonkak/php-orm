<?php

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ObjectResultSet;

class ObjectFetcher implements FetcherInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var mixed[]|null
     */
    private $constructorArguments;

    /**
     * @param string       $class
     * @param mixed[]|null $constructorArguments
     */
    public function __construct($class, array $constructorArguments = null)
    {
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
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
        return new ObjectResultSet($stmt, $this->class, $this->constructorArguments);
    }
}
