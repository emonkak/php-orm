<?php

namespace Emonkak\Orm\Relation;

interface RelationInterface
{
    /**
     * Gets the class to map.
     *
     * @return string
     */
    public function getClass();

    /**
     * Gets the reference table name.
     *
     * @return string
     */
    public function getReferenceTable();

    /**
     * Gets the reference table key.
     *
     * @return string
     */
    public function getReferenceKey();

    /**
     * Gets the outer table key selector function.
     *
     * @return callable (mixed) -> string
     */
    public function getOuterKeySelector();

    /**
     * Gets the inner table key selector function.
     *
     * @return callable (mixed) -> string
     */
    public function getInnerKeySelector();

    /**
     * Gets the result value selector function.
     *
     * @return callable (mixed, mixed) -> mixed
     */
    public function getResultValueSelector();

    /**
     * Joins between outer values and inner values.
     *
     * @param array|\Traversable $outerValues
     * @param array|\Traversable $innerValues
     * @return \Iterator
     */
    public function join($outerValues, $innerValues);
}
