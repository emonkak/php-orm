<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ArrayResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class ArrayFetcher implements FetcherInterface
{
    public function getClass(): ?string
    {
        return null;
    }

    public function fetch(PDOStatementInterface $stmt): ResultSetInterface
    {
        return new ArrayResultSet($stmt);
    }
}
