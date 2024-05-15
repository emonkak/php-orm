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

    private GrammarInterface $grammar;

    private string $prefix = 'SELECT';

    /**
     * @var Sql[]
     */
    private array $select = [];

    /**
     * @var Sql[]
     */
    private array $from = [];

    /**
     * @var Sql[]
     */
    private array $join = [];

    /**
     * @var ?Sql
     */
    private ?Sql $where = null;

    /**
     * @var Sql[]
     */
    private array $groupBy = [];

    /**
     * @var ?Sql
     */
    private ?Sql $having = null;

    /**
     * @var Sql[]
     */
    private array $window = [];

    /**
     * @var Sql[]
     */
    private array $orderBy = [];

    /**
     * @var ?int
     */
    private ?int $offset = null;

    /**
     * @var ?int
     */
    private ?int $limit = null;

    private string $suffix = '';

    /**
     * @var Sql[]
     */
    private array $union = [];

    /**
     * @var ?self
     */
    private ?self $parent = null;

    public function __construct(GrammarInterface $grammar, ?SelectBuilder $parent = null)
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

    public function select(mixed $expr, ?string $alias = null): self
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
     * @param mixed[] $exprs
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

    public function from(mixed $table, ?string $alias = null, int $position = -1): self
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

    public function where(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null, mixed $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::and($this->where, $condition) : $condition;
        return $cloned;
    }

    public function orWhere(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null, mixed $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->where = $this->where ? Sql::or($this->where, $condition) : $condition;
        return $cloned;
    }

    public function join(mixed $table, mixed $condition = null, ?string $alias = null, int $position = -1, string $type = 'JOIN'): self
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

    public function outerJoin(mixed $table, mixed $condition = null, ?string $alias = null, int $position = -1): self
    {
        return $this->join($table, $condition, $alias, $position, 'LEFT OUTER JOIN');
    }

    public function groupBy(mixed $expr): self
    {
        $expr = $this->grammar->lift($expr);
        $cloned = clone $this;
        $cloned->groupBy[] = $expr;
        return $cloned;
    }

    public function having(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null, mixed $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->having = $this->having ? Sql::and($this->having, $condition) : $condition;
        return $cloned;
    }

    public function orHaving(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null, mixed $arg4 = null): self
    {
        $condition = $this->grammar->condition(...func_get_args());
        $cloned = clone $this;
        $cloned->having = $this->having ? Sql::or($this->having, $condition) : $condition;
        return $cloned;
    }

    public function window(string $name, mixed $specification = ''): self
    {
        $specification = $this->grammar->lift($specification);
        $cloned = clone $this;
        $cloned->window[] = $this->grammar->window($name, $specification);
        return $cloned;
    }

    public function orderBy(mixed $expr, ?string $ordering = null): self
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

    public function unionWith(mixed $query, string $type = 'UNION'): self
    {
        $query = $this->grammar->lift($query);
        $cloned = clone $this;
        $cloned->union[] = $this->grammar->union($query, $type);
        return $cloned;
    }

    public function unionAllWith(mixed $query): self
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

    public function aggregate(PDOInterface $pdo, string $expr): mixed
    {
        $stmt = $this->withSelect([$expr])->withoutOrdering()->prepare($pdo);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @template T
     * @param FetcherInterface<T> $fetcher
     * @return PaginatorInterface<T>
     */
    public function paginate(FetcherInterface $fetcher, int $perPage, string $countExpr = 'COUNT(*)'): PaginatorInterface
    {
        $pdo = $fetcher->getPdo();
        $count = (int) $this->aggregate($pdo, $countExpr);
        return $this->paginateWithCount($fetcher, $perPage, $count);
    }

    /**
     * @template T
     * @param FetcherInterface<T> $fetcher
     * @return PaginatorInterface<T>
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
     * @param FetcherInterface<T> $fetcher
     * @return PageInterface<T>
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

    private function withoutOrdering(): self
    {
        $cloned = clone $this;
        $cloned->orderBy = [];
        $cloned->groupBy = [];
        return $cloned;
    }
}
