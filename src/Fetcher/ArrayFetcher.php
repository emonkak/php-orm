<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ArrayResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @implements FetcherInterface<array<string,?scalar>>
 * @use Relatable<array<string,mixed>>
 */
class ArrayFetcher implements FetcherInterface
{
    use Relatable;

    /**
     * {@inheritDoc}
     */
    public function getClass(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(PDOStatementInterface $stmt): ResultSetInterface
    {
        return new ArrayResultSet($stmt);
    }
}
