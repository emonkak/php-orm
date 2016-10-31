<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoinStrategy;

class LazyOneToMany extends Relation
{
    public function getJoinStrategy()
    {
        return new LazyOuterJoinStrategy();
    }
}
