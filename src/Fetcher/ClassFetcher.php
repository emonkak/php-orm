<?php

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ClassResultSet;

class ClassFetcher implements FetcherInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var mixed[]
     */
    private $constructorArguments;

    /**
     * @param string  $class
     * @param mixed[] $constructorArguments
     */
    public function __construct($class, array $constructorArguments = [])
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
        return new ClassResultSet($stmt, $this->class, $this->constructorArguments);
    }
}
