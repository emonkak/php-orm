<?php

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface FetcherInterface
{
    /**
     * @return ?string
     */
    public function getClass();

    /**
     * @param PDOStatementInterface $stmt
     * @return ResultSetInterface
     */
    public function fetch(PDOStatementInterface $stmt);
}
