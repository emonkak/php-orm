<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface QueryInterface extends QueryBuilderInterface
{
    /**
     * @param PDOInterface $connection
     * @return PDOStatementInterface
     */
    public function execute(PDOInterface $connection);

    /**
     * @param PDOInterface $connection
     * @param string       $class
     * @return ResultSetInterface
     */
    public function getResult(PDOInterface $connection, $class);
}
