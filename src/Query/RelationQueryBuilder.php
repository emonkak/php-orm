<?php

namespace Emonkak\Orm\Query;

use Emonkak\Orm\Relation\RelationInterface;

class RelationQueryBuilder implements RelationQueryBuilderInterface
{
    private function __construct() {}

    /**
     * @return self
     */
    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function build(array $outerValues, RelationInterface $relation)
    {
        $sql = sprintf(
            'SELECT * FROM `%s` WHERE `%s` IN (%s)',
            $relation->getInnerTable(),
            $relation->getInnerKey(),
            implode(', ', array_fill(0, count($outerValues), '?'))
        );

        $binds = array_map($relation->getOuterKeySelector(), $outerValues);

        return (new PlainQuery($sql, $binds))->withClass($relation->getClass());
    }
}
