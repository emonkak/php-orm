<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

trait Fetchable
{
    /**
     * @template TResult
     * @psalm-param FetcherInterface<TResult> $fetcher
     * @psalm-return ResultSetInterface<TResult>
     */
    public function getResult(FetcherInterface $fetcher): ResultSetInterface
    {
        '@phan-var QueryBuilderInterface $this';
        return $fetcher->fetch($this);
    }

    abstract public function prepare(PDOInterface $pdo): PDOStatementInterface;
}
