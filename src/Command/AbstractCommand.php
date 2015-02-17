<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Utils\QueryUtils;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * @return string
     */
    abstract public function getSql();

    /**
     * @return mixed[]
     */
    abstract public function getBinds();

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $pdo)
    {
        $stmt = $pdo->prepare($this->getSql());

        PDOUtils::bindTo($stmt, $this->getBinds());

        return $stmt->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return QueryUtils::toString($this->getSql(), $this->getBinds());
    }
}
