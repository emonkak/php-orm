<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Fetcher\RelationFetcher;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

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
     * @return RelationInterface[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param PDOInterface     $pdo
     * @param FetcherInterface $fetcher
     * @return ResultSetInterface
     */
    public function getResult(PDOInterface $pdo, FetcherInterface $fetcher)
    {
        $stmt = $this->prepare($pdo);

        foreach ($this->relations as $relation) {
            $fetcher = new RelationFetcher($fetcher, $relation);
        }

        return $fetcher->fetch($stmt);
    }

    /**
     * @param PDOInterface $pdo
     * @return PDOStatementInterface
     */
    abstract public function prepare(PDOInterface $pdo);
}
