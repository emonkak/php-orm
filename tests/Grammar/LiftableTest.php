<?php

namespace Emonkak\Orm\Tests\Grammar;

use Emonkak\Orm\Grammar\Liftable;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\Grammar\Liftable
 */
class LiftableTest extends \PHPUnit_Framework_TestCase
{
    private $grammar;

    public function setUp()
    {
        $this->grammar = $this->getMockForTrait(Liftable::class);
    }

    /**
     * @dataProvider providerLift
     */
    public function testLift($value, $expectedSql, array $expectedBindings)
    {
        $query = $this->grammar->lift($value);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerLift()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [(new SelectBuilder())->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
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
        $this->grammar->lift($value);
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
        $query = $this->grammar->liftValue($value);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerLiftValue()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [(new SelectBuilder())->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
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
        $this->grammar->liftValue($value);
    }

    public function providerLiftValueThrowsUnexpectedValueException()
    {
        return [
            [new \stdClass()],
        ];
    }

    public function testLiftCondition()
    {
        $query = $this->grammar->liftCondition('c1 IS NULL');
        $this->assertEquals('c1 IS NULL', $query->getSql());
        $this->assertEquals([], $query->getBindings());
    }

    public function testLiftConditionWithUnaryOperator()
    {
        $expectedQuery = Sql::literal('(c1 IS NULL)');

        $this->grammar
            ->expects($this->once())
            ->method('unaryOperator')
            ->with('IS NULL', Sql::literal('c1'))
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $this->grammar->liftCondition('c1', 'IS NULL'));
    }

    public function testLiftConditionWithOperator()
    {
        $expectedQuery = new Sql('(c1 = ?)', ['foo']);

        $this->grammar
            ->expects($this->once())
            ->method('operator')
            ->with('=', Sql::literal('c1'), Sql::value('foo'))
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $this->grammar->liftCondition('c1', '=', 'foo'));
    }

    public function testLiftConditionWithBetweenOperator()
    {
        $expectedQuery = new Sql('(c1 BETWEEN ? AND ?)', ['foo', 'bar']);

        $this->grammar
            ->expects($this->once())
            ->method('betweenOperator')
            ->with('BETWEEN', Sql::literal('c1'), Sql::value('foo'), Sql::value('bar'))
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $this->grammar->liftCondition('c1', 'BETWEEN', 'foo', 'bar'));
    }
}
