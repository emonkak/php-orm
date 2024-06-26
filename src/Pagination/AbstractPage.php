<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 * @implements PageInterface<T>
 */
abstract class AbstractPage implements PageInterface
{
    /**
     * @use EnumerableExtensions<T>
     */
    use EnumerableExtensions;

    public function getOffset(): int
    {
        return $this->getIndex() * $this->getPerPage();
    }

    public function forward(): \Traversable
    {
        $page = $this;

        yield $page;

        while ($page->hasNext()) {
            $page = $page->next();

            yield $page;
        }
    }

    public function backward(): \Traversable
    {
        $page = $this;

        yield $page;

        while ($page->hasPrevious()) {
            $page = $page->previous();

            yield $page;
        }
    }

    public function isFirst(): bool
    {
        return !$this->hasPrevious();
    }

    public function isLast(): bool
    {
        return !$this->hasNext();
    }
}
