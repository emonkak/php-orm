<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\ConditionMaker;
use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\QueryBuilderFactory;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\UpdateBuilder;

/**
 * @covers Emonkak\Orm\QueryBuilderFactory
 */
class QueryBuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testGetSelect()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);
        $builder = $factory->getSelectBuilder();

        $this->assertInstanceOf(SelectBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetInsert()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);
        $builder = $factory->getInsertBuilder();

        $this->assertInstanceOf(InsertBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetUpdate()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);
        $builder = $factory->getUpdateBuilder();

        $this->assertInstanceOf(UpdateBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetDelete()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);
        $builder = $factory->getDeleteBuilder();

        $this->assertInstanceOf(DeleteBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetGrammar()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);

        $this->assertSame($grammar, $factory->getGrammar());
    }
}
