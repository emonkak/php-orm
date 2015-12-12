<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\QueryBuilder\QueryBuilderInterface;

interface ExecutableQueryInterface extends QueryBuilderInterface
{
    /**
     * @return string
     */
    public function getClass();

    /**
     * @param PDOInterface $connection
     * @return ResultSetInterface
     */
    public function execute(PDOInterface $connection);
}
