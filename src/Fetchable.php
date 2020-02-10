<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

trait Fetchable
{
    /**
     * @var RelationInterface[]
     */
    private $relations = [];

    public function with(RelationInterface ...$relations): self
    {
        $cloned = clone $this;
        foreach ($relations as $relation) {
            $cloned->relations[] = $relation;
        }
        return $cloned;
    }

    /**
     * @return RelationInterface[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getResult(PDOInterface $pdo, FetcherInterface $fetcher): ResultSetInterface
    {
        $stmt = $this->prepare($pdo);

        $result = $fetcher->fetch($stmt);

        foreach ($this->relations as $relation) {
            $result = new RelationResultSet($result, $relation);
        }

        return $result;
    }

    abstract public function prepare(PDOInterface $pdo): PDOStatementInterface;
}
