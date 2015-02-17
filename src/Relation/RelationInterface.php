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
     * Gets the inner table name.
     *
     * @return string
     */
    public function getInnerTable();

    /**
     * Gets the inner table key.
     *
     * @return string
     */
    public function getInnerKey();

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
