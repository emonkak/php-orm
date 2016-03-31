<?php

namespace Emonkak\Orm\QueryBuilder;

interface QueryBuilderInterface extends QueryFragmentInterface
{
    /**
     * @return string
     */
    public function __toString();
}
