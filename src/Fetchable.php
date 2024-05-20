<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @psalm-require-implements QueryBuilderInterface
 */
trait Fetchable
{
    /**
     * @template TResult
     * @param FetcherInterface<TResult> $fetcher
     * @return ResultSetInterface<TResult>
     */
    public function getResult(FetcherInterface $fetcher): ResultSetInterface
    {
        return $fetcher->fetch($this);
    }

    abstract public function prepare(PDOInterface $pdo): PDOStatementInterface;
}
