<?php

namespace Emonkak\Orm\QueryBuilder;

interface QueryBuilderInterface
{
    /**
     * @return array (string, mixed[])
     */
    public function build();

    /**
     * @return string
     */
    public function __toString();
}
