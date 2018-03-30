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
    }

    /**
     * @return SelectBuilder
     */
    public function createSelect()
    {
        if ($this->selectBuilder === null) {
            $this->selectBuilder = new SelectBuilder($this->grammar);
        }
        return $this->selectBuilder;
    }

    /**
     * @return InsertBuilder
     */
    public function createInsert()
    {
        if ($this->insertBuilder === null) {
            $this->insertBuilder = new InsertBuilder($this->grammar);
        }
        return $this->insertBuilder;
    }

    /**
     * @return UpdateBuilder
     */
    public function createUpdate()
    {
        if ($this->updateBuilder === null) {
            $this->updateBuilder = new UpdateBuilder($this->grammar);
        }
        return $this->updateBuilder;
    }

    /**
     * @return DeleteBuilder
     */
    public function createDelete()
    {
        if ($this->deleteBuilder === null) {
            $this->deleteBuilder = new DeleteBuilder($this->grammar);
        }
        return $this->deleteBuilder;
    }
}
