<?php

namespace Emonkak\Orm\Tests\Grammar;

use Emonkak\Orm\Grammar\Liftable;
use Emonkak\Orm\Grammar\MySqlGrammar;
use Emonkak\Orm\Sql;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;

/**
 * @covers Emonkak\Orm\Grammar\Liftable
 */
class LiftableTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    private $liftable;

    public function setUp()
    {
        $this->liftable = $this->getMockForTrait(Liftable::class);
    }

    /**
     * @dataProvider providerLift
     */
    public function testLift($value, $expectedSql, array $expectedBindings)
    {
        $query = $this->liftable->lift($value);
        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public function providerLift()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [$this->createSelectBuilder()->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
            ['foo', 'foo', []],
        ];
    }

    /**
     * @dataProvider providerLiftThrowsUnexpectedValueException
     *
     * @expectedException UnexpectedValueException
     */
    public function testLiftThrowsUnexpectedValueException($value)
    {
        $this->liftable->lift($value);
    }

    public function providerLiftThrowsUnexpectedValueException()
    {
        return [
            [123],
            [1.23],
            [true],
            [false],
            [null],
            [[1, 2, 3]],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider providerLiftValue
     */
    public function testLiftValue($value, $expectedSql, array $expectedBindings)
    {
        $query = $this->liftable->liftValue($value);
        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public function providerLiftValue()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [$this->createSelectBuilder()->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
            ['foo', '?', ['foo']],
            [123, '?', [123]],
            [1.23, '?', [1.23]],
            [true, '?', [true]],
            [false, '?', [false]],
            [null, 'NULL', []],
            [[1, 2, 3], '(?, ?, ?)', [1, 2, 3]],
        ];
    }

    /**
     * @dataProvider providerLiftValueThrowsUnexpectedValueException
     *
     * @expectedException UnexpectedValueException
     */
    public function testLiftValueThrowsUnexpectedValueException($value)
    {
        $this->liftable->liftValue($value);
    }

    public function providerLiftValueThrowsUnexpectedValueException()
    {
        return [
            [new \stdClass()],
        ];
    }

    public function testLiftCondition()
    {
        $query = $this->liftable->liftCondition('c1 IS NULL');
        $this->assertQueryIs(
            'c1 IS NULL',
            [],
            $query
        );
    }

    public function testLiftConditionWithUnaryOperator()
    {
        $expectedQuery = new Sql('(c1 IS NULL)');

        $this->liftable
            ->expects($this->once())
            ->method('unaryOperator')
            ->with('IS NULL', new Sql('c1'))
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $this->liftable->liftCondition('c1', 'IS NULL'));
    }

    public function testLiftConditionWithOperator()
    {
        $expectedQuery = new Sql('(c1 = ?)', ['foo']);

        $this->liftable
            ->expects($this->once())
            ->method('operator')
            ->with('=', new Sql('c1'), Sql::value('foo'))
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $this->liftable->liftCondition('c1', '=', 'foo'));
    }

    public function testLiftConditionWithBetweenOperator()
    {
        $expectedQuery = new Sql('(c1 BETWEEN ? AND ?)', ['foo', 'bar']);

        $this->liftable
            ->expects($this->once())
            ->method('betweenOperator')
            ->with('BETWEEN', new Sql('c1'), Sql::value('foo'), Sql::value('bar'))
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $this->liftable->liftCondition('c1', 'BETWEEN', 'foo', 'bar'));
    }
}
