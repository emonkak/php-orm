<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ResultSet\PDOResultSet;
use Emonkak\Orm\Utils\PDOUtils;

trait Executable
{
    /**
     * @var string
     */
    protected $class = 'stdClass';

    /**
     * @param string $class
     * @return self
     */
    public function withClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $pdo)
    {
        list ($sql, $binds) = $this->compile();

        $stmt = $pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class);

        PDOUtils::bindTo($stmt, $binds);

        $stmt->execute();

        return new PDOResultSet($stmt);
    }
}
