<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

interface Spy
{
    public function __invoke(mixed ...$args): mixed;
}
