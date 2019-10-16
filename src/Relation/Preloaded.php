<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\PreloadedResultSet;

class Preloaded implements RelationStrategyInterface
{
    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var string
     */
    private $outerKey;

    /**
     * @var string
     */
    private $innerKey;

    /**
     * @var string
     */
    private $innerClass;

    /**
     * @var mixed[]
     */
    private $innerElements;

    /**
     * @param string  $relationKey
     * @param string  $outerKey
     * @param string  $innerKey
     * @param string  $innerClass
     * @param mixed[] $innerElements
     */
    public function __construct(
        $relationKey,
        $outerKey,
        $innerKey,
        $innerClass,
        array $innerElements
    ) {
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->innerClass = $innerClass;
        $this->innerElements = $innerElements;
    }

    /**
     * @return string
     */
    public function getRelationKey()
    {
        return $this->relationKey;
    }

    /**
     * @return string
     */
    public function getOuterKey()
    {
        return $this->outerKey;
    }

    /**
     * @return string
     */
    public function getInnerKey()
    {
        return $this->innerKey;
    }

    /**
     * @return string
     */
    public function getInnerClass()
    {
        return $this->innerClass;
    }

    /**
     * @return mixed[]
     */
    public function getInnerElements()
    {
        return $this->innerElements;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(array $outerKeys)
    {
        $innerKeySelector = $this->getInnerKeySelector($this->innerClass);
        $outerKeySet = array_flip($outerKeys);

        $filteredElements = [];

        foreach ($this->innerElements as $element) {
            $innerKey = $innerKeySelector($element);
            if (isset($outerKeySet[$innerKey])) {
                $filteredElements[] = $element;
            }
        }

        return new PreloadedResultSet($filteredElements, $this->innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getOuterKeySelector($outerClass)
    {
        return AccessorCreators::toKeySelector($this->outerKey, $outerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKeySelector($innerClass)
    {
        return AccessorCreators::toKeySelector($this->innerKey, $innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getResultSelector($outerClass, $innerClass)
    {
        return AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);
    }
}
