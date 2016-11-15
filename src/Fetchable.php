<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Fetcher\RelationFetcher;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @internal
 */
trait Fetchable
{
    /**
     * @var RelationInterface[]
     */
    private $relations = [];

    /**
     * @param RelationInterface $relation
     * @return $this
     */
    public function with(RelationInterface $relation)
    {
        $cloned = clone $this;
        $cloned->relations[] = $relation;
        return $cloned;
    }

    /**
     * @param PDOInterface     $connection
     * @param FetcherInterface $fetcher
     * @return ResultSetInterface
     */
    public function getResult(PDOInterface $connection, FetcherInterface $fetcher)
    {
        $stmt = $this->prepare($connection);

        foreach ($this->relations as $relation) {
            $fetcher = new RelationFetcher($fetcher, $relation);
        }

        return $fetcher->fetch($stmt);
    }

    /**
     * @param PDOInterface $connection
     * @return PDOStatementInterface
     */
    abstract public function prepare(PDOInterface $connection);
}
