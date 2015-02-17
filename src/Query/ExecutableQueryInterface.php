<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\QueryBuilder\QueryInterface;

interface ExecutableQueryInterface extends QueryInterface
{
    /**
     * @param PDOInterface $pdo
     * @return ResultSetInterface
     */
    public function execute(PDOInterface $pdo);
}
