<?php

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\Relation\RelationInterface;

class RelationFetcher implements FetcherInterface
{
    /**
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * @var RelationInterface
     */
    private $relation;

    /**
     * @param FetcherInterface  $fetcher
     * @param RelationInterface $relation
     */
    public function __construct(FetcherInterface $fetcher, RelationInterface $relation)
    {
        $this->fetcher = $fetcher;
        $this->relation = $relation;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->fetcher->getClass();
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(PDOStatementInterface $stmt)
    {
        $result = $this->fetcher->fetch($stmt);
        return new RelationResultSet($result, $this->relation);
    }
}
