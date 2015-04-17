<?php

namespace Emonkak\Orm\Relation;

class RelationBuilder
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $referenceTable;

    /**
     * @var string
     */
    private $referenceKey;

    /**
     * @var callable
     */
    private $outerKeySelector;

    /**
     * @var callable
     */
    private $innerKeySelector;

    /**
     * @var callable
     */
    private $resultValueSelector;

    /**
     * @param string $class
     * @return self
     */
    public function withClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string $referenceTable
     * @return self
     */
    public function referenceTable($referenceTable)
    {
        $this->referenceTable = $referenceTable;
        return $this;
    }

    /**
     * @param string $referenceKey
     * @return self
     */
    public function referenceKey($referenceKey)
    {
        $this->referenceKey = $referenceKey;
        return $this;
    }

    /**
     * @param string $outerKey
     * @return self
     */
    public function outerKey($outerKey)
    {
        $this->outerKeySelector = function($outerValue) use ($outerKey) {
            return $outerValue->{$outerKey};
        };
        return $this;
    }

    /**
     * @param callable $outerKeySelector
     * @return self
     */
    public function outerKeySelector(callable $outerKeySelector)
    {
        $this->outerKeySelector = $outerKeySelector;
        return $thid;
    }

    /**
     * @param string $innerKey
     * @return self
     */
    public function innerKey($innerKey)
    {
        $this->innerKey = $innerKey;
        $this->innerKeySelector = function($innerValue) use ($innerKey) {
            return $innerValue->{$innerKey};
        };
        return $this;
    }

    /**
     * @param callable $innerKeySelector
     * @return self
     */
    public function innerKeySelector(callable $innerKeySelector)
    {
        $this->innerKeySelector = $innerKeySelector;
        return $thid;
    }

    /**
     * @param string $joinKey
     * @return self
     */
    public function joinKey($joinKey)
    {
        $this->resultValueSelector = function($outerValue, $innerValue) use ($joinKey) {
            $outerValue->$joinKey = $innerValue;
            return $outerValue;
        };
        return $this;
    }

    /**
     * @param callable $resultValueKeySelector
     * @return self
     */
    public function resultValueKeySelector(callable $resultValueKeySelector)
    {
        $this->resultValueSelector = $resultValueKeySelector;
        return $this;
    }

    /**
     * @return OneToOne
     */
    public function buildOneToOne()
    {
        $this->validate();

        return new OneToOne(
            $this->class,
            $this->referenceTable,
            $this->referenceKey,
            $this->outerKeySelector,
            $this->innerKeySelector,
            $this->resultValueSelector
        );
    }

    /**
     * @return OneToMany
     */
    public function buildOneToMany()
    {
        $this->validate();

        return new OneToMany(
            $this->class,
            $this->referenceTable,
            $this->referenceKey,
            $this->outerKeySelector,
            $this->innerKeySelector,
            $this->resultValueSelector
        );
    }

    /**
     * Validates instance state.
     */
    private function validate()
    {
        if ($this->class === null
            || $this->referenceTable === null
            || $this->referenceKey === null
            || $this->outerKeySelector === null
            || $this->innerKeySelector === null
            || $this->resultValueSelector === null) {
            throw new \LogicException('Some required values has not been set.');
        }
    }
}
