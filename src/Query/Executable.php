<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\PDOResultSet;

trait Executable
{
    /**
     * @var string
     */
    private $class = 'stdClass';

    /**
     * @param string $class
     * @return self
     */
    public function to($class)
    {
        $chained = clone $this;
        $chained->class = $class;
        return $chained;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $pdo)
    {
        return $this->toExecutable()->execute($pdo);
    }

    /**
     * @return ExecutableQueryInterface
     */
    private function toExecutable()
    {
        return new ExecutableQuery($this, $this->class);
    }
}
