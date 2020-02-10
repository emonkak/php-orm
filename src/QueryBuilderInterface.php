<?php

declare(strict_types=1);

namespace Emonkak\Orm;

interface QueryBuilderInterface
{
    public function build(): Sql;
}
