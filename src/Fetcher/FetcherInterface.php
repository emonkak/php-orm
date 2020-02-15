<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T
 */
interface FetcherInterface
{
    public function getPdo(): PDOInterface;

    /**
     * @psalm-return ?class-string<T>
     */
    public function getClass(): ?string;

    /**
     * @psalm-return ResultSetInterface<T>
     */
    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface;
}
