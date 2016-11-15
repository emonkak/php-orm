<?php

namespace Emonkak\Orm;

interface QueryBuilderInterface
{
    /**
     * @return Sql
     */
    public function build();
}
