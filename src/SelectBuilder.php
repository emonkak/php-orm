<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Pagination\PageInterface;
use Emonkak\Orm\Pagination\PaginatorInterface;
use Emonkak\Orm\Pagination\PrecountPaginator;
use Emonkak\Orm\Pagination\SequentialPage;

/**
 * Provides the query building of SELECT statement.
 */
class SelectBuilder implements QueryBuilderInterface
{
    use Aggregatable;
    use Explainable;
    use Fetchable;
    use Preparable;

    /**
     * @var GrammarInterface
     */
    private $grammar;

    /**
     * @var string
     */
    private $prefix = 'SELECT';

    /**
     * @var Sql[]
     */
    private $select = [];

    /**
     * @var Sql[]
     */
    private $from = [];

    /**
     * @var Sql[]
     */
    private $join = [];

    /**
     * @var ?Sql
     */
    private $where;

    /**
     * @var Sql[]
     */
    private $groupBy = [];

    /**
     * @var ?Sql
     */
    private $having;

    /**
     * @var Sql[]
     */
    private $window = [];

    /**
     * @var Sql[]
     */
    private $orderBy = [];

    /**
     * @var ?int
     */
    private $offset;

    /**
     * @var ?int
     */
    private $limit;

    /**
     * @var string
     */
    private $suffix = '';

    /**
     * @var Sql[]
     */
    private $union = [];

    /**
     * @var ?self
     */
    private $parent;

    public function __construct(GrammarInterface $grammar, SelectBuilder $parent = null)
    {
        $this->grammar = $grammar;
        $this->parent = $parent;
    }

    public function getGrammar(): GrammarInterface
    {
        return $this->grammar;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return Sql[]
     */
    public function getSelectBuilder(): array
    {
        return $this->select;
    }

    /**
     * @return Sql[]
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @return Sql[]
     */
    public function getJoin(): array
    {
        return $this->join;
    }

    /**
     * @return ?Sql
     */
    public function getWhere(): ?Sql
    {
        return $this->where;
    }

    /**
     * @return Sql[]
     */
    public function getGroupby(): array
    {
        return $this->groupBy;
    }

    /**
     * @return ?Sql
     */
    public function getHaving(): ?Sql
    {
        return $this->having;
    }

    /**
     * @return Sql[]
     */
    public function getWindow(): array
    {
        return $this->window;
    }

    /**
     * @return Sql[]
     */
    public function getOrderby(): array
    {
        return $this->orderBy;
    }

    /**
     * @return ?int
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return ?int
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @return Sql[]
     */
    public function getUnion(): array
    {
        return $this->union;
    }

    public function prefix(string $prefix): self
    {
        $cloned = clone $this;
        $cloned->prefix = $prefix;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface|Sql|string $expr
     */
    public function select($expr, ?string $alias = null): self
    {
        $expr = $this->grammar->lift($expr);
        if ($alias !== null) {
            $expr = $this->grammar->alias($expr, $alias);
        }
        $cloned = clone $this;
        $cloned->select[] = $expr;
        return $cloned;
    }

    /**
     * @param (QueryBuilderInterface|Sql|string)[] $exprs
     */
    public function withSelect(array $exprs): self
    {
        $select = [];
        foreach ($exprs as $key => $expr) {
            $expr = $this->grammar->lift($expr);
            if (is_string($key)) {
                $expr = $this->grammar->alias($expr, $key);
            }
            $select[] = $expr;
        }
        $cloned = clone $this;
        $cloned->select = $select;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface|Sql|string $table
     */
    public function from($table, ?string $alias = null, int $position = -1): self
    {
        $table = $this->grammar->lift($table);
        if ($alias !== null) {
            $table = $this->grammar->alias($table, $alias);
        }
        $from = $this->from;
        if ($position < 0) {
            $from[] = $table;
        } else {
            array_splice($from, $position, 0, [$table]);
        }
        $cloned = clone $this;
        $cloned->from = $from;
        return $cloned;
    }

    /**
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg4
     */
    public function where($arg1, $arg2 = null, $arg3 = null, $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::_and($this->where, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg4
     */
    public function orWhere($arg1, $arg2 = null, $arg3 = null, $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::_or($this->where, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface|Sql|string $table
     * @param QueryBuilderInterface|Sql|string|null $condition
     */
    public function join($table, $condition = null, string $alias = null, int $position = -1, string $type = 'JOIN'): self
    {
        $table = $this->grammar->lift($table);
        if ($alias !== null) {
            $table = $this->grammar->alias($table, $alias);
        }
        if ($condition !== null) {
            $condition = $this->grammar->lift($condition);
        }
        $joinedTable = $this->grammar->join($table, $condition, $type);
        $join = $this->join;
        if ($position < 0) {
            $join[] = $joinedTable;
        } else {
            array_splice($join, $position, 0, [$joinedTable]);
        }
        $cloned = clone $this;
        $cloned->join = $join;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface|Sql|string $table
     * @param QueryBuilderInterface|Sql|string|null $condition
     */
    public function outerJoin($table, $condition = null, string $alias = null, int $position = -1): self
    {
        return $this->join($table, $condition, $alias, $position, 'LEFT OUTER JOIN');
    }

    /**
     * @param QueryBuilderInterface|Sql|string $expr
     */
    public function groupBy($expr): self
    {
        $expr = $this->grammar->lift($expr);
        $cloned = clone $this;
        $cloned->groupBy[] = $expr;
        return $cloned;
    }

    /**
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg4
     */
    public function having($arg1, $arg2 = null, $arg3 = null, $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->having = $this->having ? Sql::_and($this->having, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg4
     */
    public function orHaving($arg1, $arg2 = null, $arg3 = null, $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->having = $this->having ? Sql::_or($this->having, $condition) : $condition;
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface|Sql|string $specification
     */
    public function window(string $name, $specification = ''): self
    {
        $specification = $this->grammar->lift($specification);
        $cloned = clone $this;
        $cloned->window[] = $this->grammar->window($name, $specification);
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface|Sql|string $expr
     */
    public function orderBy($expr, ?string $ordering = null): self
    {
        $expr = $this->grammar->lift($expr);
        if ($ordering !== null) {
            $expr = $this->grammar->ordering($expr, $ordering);
        }
        $cloned = clone $this;
        $cloned->orderBy[] = $expr;
        return $cloned;
    }

    public function limit(int $limit): self
    {
        $cloned = clone $this;
        $cloned->limit = $limit;
        return $cloned;
    }

    public function offset(int $offset): self
    {
        $cloned = clone $this;
        $cloned->offset = $offset;
        return $cloned;
    }

    public function suffix(string $suffix): self
    {
        $cloned = clone $this;
        $cloned->suffix = $suffix;
        return $cloned;
    }

    public function forUpdate(): self
    {
        return $this->suffix('FOR UPDATE');
    }

    public function union(string $type = 'UNION'): self
    {
        $parent = clone $this;
        $parent->suffix = ltrim($parent->suffix . ' ' . $type, ' ');
        return new SelectBuilder($this->grammar, $parent);
    }

    public function unionAll(): self
    {
        return $this->union('UNION ALL');
    }

    /**
     * @param QueryBuilderInterface|Sql|string $query
     */
    public function unionWith($query, string $type = 'UNION'): self
    {
        $query = $this->grammar->lift($query);
        $cloned = clone $this;
        $cloned->union[] = $this->grammar->union($query, $type);
        return $cloned;
    }

    /**
     * @param QueryBuilderInterface|Sql|string $query
     */
    public function unionAllWith($query): self
    {
        return $this->unionWith($query, 'UNION ALL');
    }

    public function build(): Sql
    {
        $queryBuilder = $this;
        $sqls = [];

        do {
            $sqls[] = $this->grammar->selectStatement(
                $queryBuilder->prefix,
                $queryBuilder->select,
                $queryBuilder->from,
                $queryBuilder->join,
                $queryBuilder->where,
                $queryBuilder->groupBy,
                $queryBuilder->having,
                $queryBuilder->window,
                $queryBuilder->orderBy,
                $queryBuilder->limit,
                $queryBuilder->offset,
                $queryBuilder->suffix,
                $queryBuilder->union
            );
        } while ($queryBuilder = $queryBuilder->parent);

        return count($sqls) > 1 ? Sql::join(' ', array_reverse($sqls)) : $sqls[0];
    }

    /**
     * @return ?scalar
     */
    public function aggregate(PDOInterface $pdo, string $expr)
    {
        $stmt = $this->withSelect([$expr])->withoutSorting()->prepare($pdo);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @template T
     * @psalm-param FetcherInterface<T> $fetcher
     * @psalm-return PaginatorInterface<T>
     */
    public function paginate(FetcherInterface $fetcher, int $perPage, string $countExpr = 'COUNT(*)'): PaginatorInterface
    {
        $pdo = $fetcher->getPdo();
        $count = (int) $this->aggregate($pdo, $countExpr);
        return $this->paginateWithCount($fetcher, $perPage, $count);
    }

    /**
     * @template T
     * @psalm-param FetcherInterface<T> $fetcher
     * @psalm-return PaginatorInterface<T>
     */
    public function paginateWithCount(FetcherInterface $fetcher, int $perPage, int $count): PaginatorInterface
    {
        $itemsFetcher = function(int $offset, int $limit) use ($fetcher): \Traversable {
            return $this
                ->limit($limit)
                ->offset($offset)
                ->getResult($fetcher);
        };
        return new PrecountPaginator($perPage, $count, $itemsFetcher);
    }

    /**
     * @template T
     * @psalm-param FetcherInterface<T> $fetcher
     * @psalm-return PageInterface<T>
     */
    public function paginateFrom(FetcherInterface $fetcher, int $initialIndex, int $perPage): PageInterface
    {
        $itemsFetcher = function(int $offset, int $limit) use ($fetcher): array {
            return $this
                ->limit($limit)
                ->offset($offset)
                ->getResult($fetcher)
                ->toArray();
        };
        return SequentialPage::from($initialIndex, $perPage, $itemsFetcher);
    }

    private function withoutSorting(): self
    {
        $cloned = clone $this;
        $cloned->orderBy = [];
        $cloned->groupBy = [];
        return $cloned;
    }
}
