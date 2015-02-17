<?php

namespace Emonkak\Orm\Relation;

abstract class AbstractRelation implements RelationInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $innerTable;

    /**
     * @var string
     */
    protected $innerKey;

    /**
     * @var \Closure
     */
    protected $outerKeySelector;

    /**
     * @var \Closure
     */
    protected $innerKeySelector;

    /**
     * @var \Closure
     */
    protected $resultValueSelector;

    /**
     * @param string   $class
     * @param string   $innerTable
     * @param string   $innerKey
     * @param \Closure $outerKeySelector
     * @param \Closure $innerKeySelector
     * @param \Closure $resultValueSelector
     */
    public function __construct(
        $class,
        $innerTable,
        $innerKey,
        \Closure $outerKeySelector,
        \Closure $innerKeySelector,
        \Closure $resultValueSelector
    ) {
        $this->class = $class;
        $this->innerTable = $innerTable;
        $this->innerKey = $innerKey;
        $this->outerKeySelector = $outerKeySelector;
        $this->innerKeySelector = $innerKeySelector;
        $this->resultValueSelector = $resultValueSelector;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerTable()
    {
        return $this->innerTable;
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKey()
    {
        return $this->innerKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getOuterKeySelector()
    {
        return $this->outerKeySelector;
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKeySelector()
    {
        return $this->innerKeySelector;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultValueSelector()
    {
        return $this->resultValueSelector;
    }
}
