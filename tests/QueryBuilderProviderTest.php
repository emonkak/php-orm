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
    public function testGetSelect()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $provider = QueryBuilderProvider::create($grammar);
        $builder = $provider->getSelect();

        $this->assertInstanceOf(SelectBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetInsert()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $provider = QueryBuilderProvider::create($grammar);
        $builder = $provider->getInsert();

        $this->assertInstanceOf(InsertBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetUpdate()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $provider = QueryBuilderProvider::create($grammar);
        $builder = $provider->getUpdate();

        $this->assertInstanceOf(UpdateBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetDelete()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $provider = QueryBuilderProvider::create($grammar);
        $builder = $provider->getDelete();

        $this->assertInstanceOf(DeleteBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }
}
