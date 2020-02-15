<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;

interface QueryBuilderInterface
{
    public function prepare(PDOInterface $pdo): PDOStatementInterface;

    public function build(): Sql;
}
