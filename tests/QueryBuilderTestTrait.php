<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\MySqlGrammar;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

trait QueryBuilderTestTrait
{
    protected function assertQueryIs($expectedSql, array $expectedBindings, Sql $query)
    {
        $this->assertSame($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    protected function createSelectBuilder()
    {
        return new SelectBuilder(new MySqlGrammar());
    }

    protected function createInsertBuilder()
    {
        return new InsertBuilder(new MySqlGrammar());
    }

    protected function createUpdateBuilder()
    {
        return new UpdateBuilder(new MySqlGrammar());
    }

    protected function createDeleteBuilder()
    {
        return new DeleteBuilder(new MySqlGrammar());
    }
}
