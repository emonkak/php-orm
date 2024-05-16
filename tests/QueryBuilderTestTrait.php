<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

trait QueryBuilderTestTrait
{
    /**
     * @param mixed[] $expectedBindings
     */
    protected function assertQueryIs(string $expectedSql, array $expectedBindings, Sql $query): void
    {
        $this->assertSame($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    protected function getSelectBuilder(): SelectBuilder
    {
        return new SelectBuilder(new DefaultGrammar());
    }

    protected function getInsertBuilder(): InsertBuilder
    {
        return new InsertBuilder(new DefaultGrammar());
    }

    protected function getUpdateBuilder(): UpdateBuilder
    {
        return new UpdateBuilder(new DefaultGrammar());
    }

    protected function getDeleteBuilder(): DeleteBuilder
    {
        return new DeleteBuilder(new DefaultGrammar());
    }
}
