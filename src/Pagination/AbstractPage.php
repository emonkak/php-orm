<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 * @implements \IteratorAggregate<T>
 * @implements PageInterface<T>
 */
abstract class AbstractPage implements \IteratorAggregate, PageInterface
{
    /**
     * @use EnumerableExtensions<T>
     */
    use EnumerableExtensions;

    public function getOffset(): int
    {
        return $this->getIndex() * $this->getPerPage();
    }

    /**
     * {@inheritdoc}
     */
    public function forward(): iterable
    {
        $page = $this;

        yield $page;

        while ($page->hasNext()) {
            $page = $page->next();

            yield $page;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function backward(): iterable
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
