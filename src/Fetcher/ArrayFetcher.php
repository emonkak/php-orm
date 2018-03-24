<?php

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ArrayResultSet;

class ArrayFetcher implements FetcherInterface
{
    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(PDOStatementInterface $stmt)
    {
        return new ArrayResultSet($stmt);
    }
}
