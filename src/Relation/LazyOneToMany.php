<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoinStrategy;

class LazyOneToMany extends Relation
{
    public function getJoinStrategy()
    {
        return new LazyGroupJoinStrategy();
    }
}
