<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface FetcherInterface
{
    /**
     * @return ?class-string
     */
    public function getClass(): ?string;

    public function fetch(PDOStatementInterface $stmt): ResultSetInterface;
}
