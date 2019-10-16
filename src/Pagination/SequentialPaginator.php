<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

class SequentialPaginator implements \IteratorAggregate, PaginatorInterface
{
    use EnumerableExtensions;

    /**
     * @var callable(int,int):mixed[]
     */
    private $resultFetcher;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @param callable(int,int):mixed[] $resultFetcher
     * @param int                       $perPage
     */
    public function __construct(callable $resultFetcher, $perPage)
    {
        $this->resultFetcher = $resultFetcher;
        $this->perPage = $perPage;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $page = $this->at(0);

        foreach ($page as $item) {
            yield $item;
        }

        while ($page->hasNext()) {
            $page = $page->next();

            foreach ($page as $item) {
                yield $item;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function at($index)
    {
        $resultFetcher = $this->resultFetcher;
        $result = $resultFetcher($this->perPage + 1, $index * $this->perPage);
        return new SequentialPage($result, $index, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function getPerPage()
    {
        return $this->perPage;
    }
}
