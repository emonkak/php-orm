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
    protected $referenceTable;

    /**
     * @var string
     */
    protected $referenceKey;

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
     * @param string   $referenceTable
     * @param string   $referenceKey
     * @param \Closure $outerKeySelector
     * @param \Closure $innerKeySelector
     * @param \Closure $resultValueSelector
     */
    public function __construct(
        $class,
        $referenceTable,
        $referenceKey,
        \Closure $outerKeySelector,
        \Closure $innerKeySelector,
        \Closure $resultValueSelector
    ) {
        $this->class = $class;
        $this->referenceTable = $referenceTable;
        $this->referenceKey = $referenceKey;
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
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * {@inheritDoc}
     */
    public function getReferenceKey()
    {
        return $this->referenceKey;
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
