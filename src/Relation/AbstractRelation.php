<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ExecutableQueryInterface;
use Emonkak\Orm\ResultSet\IteratorResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

abstract class AbstractRelation implements RelationInterface
{
    /**
     * {@inheritDoc}
     */
    public function join(ResultSetInterface $outer, $outerClass)
    {
        $outerValues = $outer->all();
        $inner = $this->getResult($outerValues, $outerClass);
        $joined = $this->doJoin($outerValues, $outerClass, $inner);
        return new IteratorResultSet($joined);
    }

    /**
     * Get the result for this relation query.
     *
     * @param object[] $outerValues
     * @param string   $outerClass
     * @return ResultSetInterface
     */
    abstract protected function getResult(array $outerValues, $outerClass);

    /**
     * Joins between outer values and inner values.
     *
     * @param object[]           $outerValues
     * @param string             $outerClass
     * @param ResultSetInterface $inner
     * @return \Iterator
     */
    abstract protected function doJoin(array $outerValues, $outerClass, ResultSetInterface $inner);
}
