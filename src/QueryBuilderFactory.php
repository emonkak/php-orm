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
    public function getSelectBuilder()
    {
        return new SelectBuilder($this->grammar);
    }

    /**
     * @return InsertBuilder
     */
    public function getInsertBuilder()
    {
        return new InsertBuilder($this->grammar);
    }

    /**
     * @return UpdateBuilder
     */
    public function getUpdateBuilder()
    {
        return new UpdateBuilder($this->grammar);
    }

    /**
     * @return DeleteBuilder
     */
    public function getDeleteBuilder()
    {
        return new DeleteBuilder($this->grammar);
    }

    /**
     * @return GrammarInterface
     */
    public function getGrammar()
    {
        return $this->grammar;
    }
}
