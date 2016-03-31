<?php

namespace Emonkak\Orm\QueryBuilder;

interface QueryFragmentInterface
{
    /**
     * @return array (string, mixed[])
     */
    public function build();
}
