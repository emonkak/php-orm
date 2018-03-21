<?php

namespace Emonkak\Orm\Tests;

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

    public function testCreateSelect()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);
        $builder = $factory->createSelect();

        $this->assertInstanceOf(SelectBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testCreateInsert()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);
        $builder = $factory->createInsert();

        $this->assertInstanceOf(InsertBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testCreateUpdate()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);
        $builder = $factory->createUpdate();

        $this->assertInstanceOf(UpdateBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testCreateDelete()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $factory = new QueryBuilderFactory($grammar);
        $builder = $factory->createDelete();

        $this->assertInstanceOf(DeleteBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }
}
