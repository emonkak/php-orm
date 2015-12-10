<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\QueryBuilder\QueryBuilderInterface;

interface QueryInterface extends QueryBuilderInterface
{
    /**
     * @param PDOInterface $pdo
     * @return ResultSetInterface
     */
    public function execute(PDOInterface $pdo);
}
