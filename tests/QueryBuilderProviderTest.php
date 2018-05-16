<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\ConditionMaker;
use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\QueryBuilderProvider;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\UpdateBuilder;

/**
 * @covers Emonkak\Orm\QueryBuilderProvider
 */
class QueryBuilderProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testSelect()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $provider = QueryBuilderProvider::create($grammar);
        $builder = $provider->select();

        $this->assertInstanceOf(SelectBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testInsert()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $provider = QueryBuilderProvider::create($grammar);
        $builder = $provider->insert();

        $this->assertInstanceOf(InsertBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testUpdate()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $provider = QueryBuilderProvider::create($grammar);
        $builder = $provider->update();

        $this->assertInstanceOf(UpdateBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testDelete()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $provider = QueryBuilderProvider::create($grammar);
        $builder = $provider->delete();

        $this->assertInstanceOf(DeleteBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }
}
