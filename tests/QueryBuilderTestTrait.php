<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\DefaultGrammar;
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
        return new SelectBuilder(new DefaultGrammar());
    }

    protected function createInsertBuilder()
    {
        return new InsertBuilder(new DefaultGrammar());
    }

    protected function createUpdateBuilder()
    {
        return new UpdateBuilder(new DefaultGrammar());
    }

    protected function createDeleteBuilder()
    {
        return new DeleteBuilder(new DefaultGrammar());
    }
}
