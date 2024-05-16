<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

/**
 * @template T
 * @extends PageInterface<T>
 * @method self<T> next()
 * @method self<T> previous()
 * @method iterable<self<T>> forward()
 * @method iterable<self<T>> backward()
 */
interface PaginatablePageInterface extends PageInterface
{
    /**
     * @return PaginatorInterface<T>
     */
    public function getPaginator(): PaginatorInterface;
}
