<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

class QueryBuilderFactory
{
    /**
     * @var GrammarInterface
     */
    private $grammar;

    /**
     * @param $grammar GrammarInterface
     */
    public function __construct(GrammarInterface $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * @return SelectBuilder
     */
    public function createSelect()
    {
        return new SelectBuilder($this->grammar);
    }

    /**
     * @return InsertBuilder
     */
    public function createInsert()
    {
        return new InsertBuilder($this->grammar);
    }

    /**
     * @return UpdateBuilder
     */
    public function createUpdate()
    {
        return new UpdateBuilder($this->grammar);
    }

    /**
     * @return DeleteBuilder
     */
    public function createDelete()
    {
        return new DeleteBuilder($this->grammar);
    }

    /**
     * @return ConditionMaker
     */
    public function createConditionMaker()
    {
        return new ConditionMaker($this->grammar);
    }
}
