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
     * @template T
     * @psalm-param FetcherInterface<T> $fetcher
     * @psalm-return ResultSetInterface<T>
     */
    public function getResult(PDOInterface $pdo, FetcherInterface $fetcher): ResultSetInterface
    {
        $stmt = $this->prepare($pdo);

        return $fetcher->fetch($stmt);
    }

    abstract public function prepare(PDOInterface $pdo): PDOStatementInterface;
}
