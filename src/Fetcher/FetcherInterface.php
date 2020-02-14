<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T
 */
interface FetcherInterface
{
    /**
     * @psalm-return ?class-string<T>
     */
    public function getClass(): ?string;

    /**
     * @psalm-return ResultSetInterface<T>
     */
    public function fetch(PDOStatementInterface $stmt): ResultSetInterface;
}
