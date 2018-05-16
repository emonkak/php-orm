<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Grammar\GrammarInterface;

class QueryBuilderProvider
{
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
     * @param GrammarInterface $grammar
     * @return QueryBuilderProvider
     */
    public static function create(GrammarInterface $grammar)
    {
        return new QueryBuilderProvider(
            new SelectBuilder($grammar),
            new InsertBuilder($grammar),
            new UpdateBuilder($grammar),
            new DeleteBuilder($grammar)
        );
    }

    /**
     * @param SelectBuilder $selectBuilder
     * @param InsertBuilder $insertBuilder
     * @param UpdateBuilder $updateBuilder
     * @param DeleteBuilder $deleteBuilder
     */
    public function __construct(
        SelectBuilder $selectBuilder,
        InsertBuilder $insertBuilder,
        UpdateBuilder $updateBuilder,
        DeleteBuilder $deleteBuilder
    ) {
        $this->selectBuilder = $selectBuilder;
        $this->insertBuilder = $insertBuilder;
        $this->updateBuilder = $updateBuilder;
        $this->deleteBuilder = $deleteBuilder;
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
