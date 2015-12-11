<?php

namespace Emonkak\Orm\Relation;

use Emonkak\QueryBuilder\Expression\ExpressionResolver;
use Emonkak\QueryBuilder\Expression\ExpressionInterface;

class ManyToManyBuilder extends AbstractRelationBuilder
{
    /**
     * @var stirng
     */
    private $intersectionTable;

    /**
     * @var mixed
     */
    private $intersectionCondition;

    /**
     * @param string $intersectionTable
     * @return self
     */
    public function intersectionTable($intersectionTable)
    {
        $this->intersectionTable = $intersectionTable;
        return $this;
    }

    /**
     * @param mixed $intersectionCondition
     * @return self
     */
    public function intersectionCondition($intersectionCondition)
    {
        $this->intersectionCondition = $intersectionCondition;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return new ManyToMany(
            $this->resolvePdo(),
            $this->resolveInnerClass(),
            $this->resolveForeignTable(),
            $this->resolveForeignKey(),
            $this->resolveIntersectionTable(),
            $this->resolveIntersectionCondition(),
            $this->resolveOuterKeySelector(),
            $this->resolveInnerKeySelector(),
            $this->resolveResultValueSelector()
        );
    }

    /**
     * @return string
     */
    protected function resolveIntersectionTable()
    {
        if ($this->intersectionTable !== null) {
            return $this->intersectionTable;
        }
        throw new \LogicException('"$intersectionTable" has not been set');
    }

    /**
     * @return ExpressionInterface
     */
    protected function resolveIntersectionCondition()
    {
        if ($this->intersectionCondition !== null) {
            return ExpressionResolver::resolveCreteria($this->intersectionCondition);
        }
        throw new \LogicException('"$intersectionCondition" has not been set');
    }
}
