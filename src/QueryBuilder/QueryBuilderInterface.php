<?php

namespace Emonkak\Orm\QueryBuilder;

interface QueryBuilderInterface
{
    /**
     * @return Sql
     */
    public function build();
}
