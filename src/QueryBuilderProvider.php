<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

class QueryBuilderProvider
{
    /**
     * @var GrammarInterface
     */
    private $grammar;

    /**
     * @var SelectBuilder
     */
    private $selectBuilder;

    /**
     * @var InsertBuilder
     */
    private $insertBuilder;

    /**
     * @var UpdateBuilder
     */
    private $updateBuilder;

    /**
     * @var DeleteBuilder
     */
    private $deleteBuilder;

    /**
     * @param $grammar GrammarInterface
     */
    public function __construct(GrammarInterface $grammar)
    {
        $this->grammar = $grammar;
        $this->selectBuilder = new SelectBuilder($this->grammar);
        $this->insertBuilder = new InsertBuilder($this->grammar);
        $this->updateBuilder = new UpdateBuilder($this->grammar);
        $this->deleteBuilder = new DeleteBuilder($this->grammar);
    }

    /**
     * @return GrammarInterface
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * @return SelectBuilder
     */
    public function select()
    {
        return $this->selectBuilder;
    }

    /**
     * @return InsertBuilder
     */
    public function insert()
    {
        return $this->insertBuilder;
    }

    /**
     * @return UpdateBuilder
     */
    public function update()
    {
        return $this->updateBuilder;
    }

    /**
     * @return DeleteBuilder
     */
    public function delete()
    {
        return $this->deleteBuilder;
    }
}
