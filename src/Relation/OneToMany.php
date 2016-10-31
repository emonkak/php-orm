<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\GroupJoinStrategy;

class OneToMany extends Relation
{
    public function getJoinStrategy()
    {
        return new GroupJoinStrategy();
    }
}
