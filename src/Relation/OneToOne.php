<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\OuterJoinStrategy;

class OneToOne extends Relation
{
    public function getJoinStrategy()
    {
        return new OuterJoinStrategy();
    }
}
