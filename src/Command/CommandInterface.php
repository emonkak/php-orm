<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;

interface CommandInterface
{
    /**
     * @param PDOInterface $pdo
     * @return integer
     */
    public function execute(PDOInterface $pdo);

    /**
     * @return string
     */
    public function __toString();
}
