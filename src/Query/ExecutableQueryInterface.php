<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\QueryBuilder\QueryBuilderInterface;

interface ExecutableQueryInterface extends QueryBuilderInterface
{
    /**
     * @param PDOInterface $pdo
     * @return ResultSetInterface
     */
    public function execute(PDOInterface $pdo);
}
