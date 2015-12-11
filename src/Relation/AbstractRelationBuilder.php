<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;

abstract class AbstractRelationBuilder
{
    /**
     * @var PDOInterface
     */
    private $pdo;

    /**
     * @var string
     */
    private $innerClass;

    /**
     * @var string
     */
    private $foreignTable;

    /**
     * @var string
     */
    private $foreignKey;

    /**
     * @var string
     */
    private $outerKey;

    /**
     * @var callable
     */
    private $outerKeySelector;

    /**
     * @var string
     */
    private $innerKey;

    /**
     * @var callable
     */
    private $innerKeySelector;

    /**
     * @var callable
     */
    private $resultValueSelector;

    /**
     * @param PDOInterface $pdo
     * @return self
     */
    public function pdo(PDOInterface $pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * @param string $innerClass
     * @return self
     */
    public function innerClass($innerClass)
    {
        $this->innerClass = $innerClass;
        return $this;
    }

    /**
     * @param string $foreignTable
     * @return self
     */
    public function foreignTable($foreignTable)
    {
        $this->foreignTable = $foreignTable;
        return $this;
    }

    /**
     * @param string $foreignKey
     * @return self
     */
    public function foreignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;
        return $this;
    }

    /**
     * @param string $outerKey
     * @return self
     */
    public function outerKey($outerKey)
    {
        $this->outerKey = $outerKey;
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
        return $this;
    }

    /**
     * @param callable $innerKeySelector
     * @return self
     */
    public function innerKeySelector(callable $innerKeySelector)
    {
        $this->innerKeySelector = $innerKeySelector;
        return $this;
    }

    /**
     * @param string $joinKey
     * @return self
     */
    public function joinKey($joinKey)
    {
        $this->joinKey = $joinKey;
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
     * @return RelationInterface
     */
    abstract public function build();

    /**
     * @return PDOInterface
     */
    protected function resolvePDO()
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }
        throw new \LogicException('"$pdo" has not been set');
    }

    /**
     * @return sting
     */
    protected function resolveInnerClass()
    {
        if ($this->innerClass !== null) {
            return $this->innerClass;
        }
        throw new \LogicException('"$innerClass" has not been set');
    }

    /**
     * @return sting
     */
    protected function resolveForeignTable()
    {
        if ($this->foreignTable !== null) {
            return $this->foreignTable;
        }
        throw new \LogicException('"$foreignTable" has not been set');
    }

    /**
     * @return sting
     */
    protected function resolveForeignKey()
    {
        if ($this->foreignKey !== null) {
            return $this->foreignKey;
        }
        throw new \LogicException('"$foreignKey" has not been set');
    }

    /**
     * @return callable
     */
    protected function resolveOuterKeySelector()
    {
        if ($this->outerKeySelector !== null) {
            return $this->outerKeySelector;
        }
        if ($this->outerKey !== null) {
            $outerKey = $this->outerKey;
            return function($outerValue) use ($outerKey) {
                return $outerValue->{$outerKey};
            };
        }
        if ($this->foreignKey !== null) {
            $outerKey = $this->foreignKey;
            return function($outerValue) use ($outerKey) {
                return $outerValue->{$outerKey};
            };
        }
        throw new \LogicException('"$outerKeySelector" has not been set');
    }

    /**
     * @return callable
     */
    protected function resolveInnerKeySelector()
    {
        if ($this->innerKeySelector !== null) {
            return $this->innerKeySelector;
        }
        if ($this->innerKey !== null) {
            $innerKey = $this->innerKey;
            return function($innerValue) use ($innerKey) {
                return $innerValue->{$innerKey};
            };
        }
        if ($this->foreignKey !== null) {
            $innerKey = $this->foreignKey;
            return function($innerValue) use ($innerKey) {
                return $innerValue->{$innerKey};
            };
        }
        throw new \LogicException('"$innerKeySelector" has not been set');
    }

    /**
     * @return callable
     */
    protected function resolveResultValueSelector()
    {
        if ($this->resultValueSelector !== null) {
            return $this->resultValueSelector;
        }
        if ($this->joinKey !== null) {
            $joinKey = $this->joinKey;
            return function($outerValue, $innerValue) use ($joinKey) {
                $outerValue->{$joinKey} = $innerValue;
                return $outerValue;
            };
        }
        throw new \LogicException('"$resultValueSelector" has not been set');
    }
}
