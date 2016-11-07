<?php

namespace Emonkak\Orm\QueryBuilder\Compiler;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;

interface CompilerInterface
{
    public function compileSelect($prefix, array $select, array $from, array $join, QueryBuilderInterface $where = null, array $groupBy, QueryBuilderInterface $having = null, array $orderBy, $limit, $offset, $suffix, array $union);
}
