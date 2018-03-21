<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

class QueryBuilderFactory
{
    /**
     * @var GrammarInterface
     */
    private $grammer;

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
     * @param $grammer GrammarInterface
     */
    public function __construct(GrammarInterface $grammer)
    {
        $this->grammer = $grammer;
    }

    /**
     * @return SelectBuilder
     */
    public function createSelect()
    {
        if ($this->selectBuilder === null) {
            $this->selectBuilder = new SelectBuilder($this->grammer);
        }
        return $this->selectBuilder;
    }

    /**
     * @return InsertBuilder
     */
    public function createInsert()
    {
        if ($this->insertBuilder === null) {
            $this->insertBuilder = new InsertBuilder($this->grammer);
        }
        return $this->insertBuilder;
    }

    /**
     * @return UpdateBuilder
     */
    public function createUpdate()
    {
        if ($this->updateBuilder === null) {
            $this->updateBuilder = new UpdateBuilder($this->grammer);
        }
        return $this->updateBuilder;
    }

    /**
     * @return DeleteBuilder
     */
    public function createDelete()
    {
        if ($this->deleteBuilder === null) {
            $this->deleteBuilder = new DeleteBuilder($this->grammer);
        }
        return $this->deleteBuilder;
    }
}
