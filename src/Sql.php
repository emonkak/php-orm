<?php

declare(strict_types=1);

namespace Emonkak\Orm;

class Sql implements QueryBuilderInterface
{
    use Explainable;
    use Fetchable;
    use Preparable;

    private string $sql;

    /**
     * @var array<int,mixed>
     */
    private array $bindings;

    public static function format(string $format, Sql ...$sqls): self
    {
        $tmpSqls = [];
        $tmpBindings = [];

        foreach ($sqls as $sql) {
            $tmpSqls[] = $sql->sql;
            $tmpBindings[] = $sql->bindings;
        }

        $sql = vsprintf($format, $tmpSqls);
        $bindings = array_merge(...$tmpBindings);

        return new Sql($sql, $bindings);
    }

    /**
     * @param Sql[] $queries
     */
    public static function join(string $separator, array $queries): self
    {
        $tmpSqls = [];
        $tmpBindings = [];

        foreach ($queries as $query) {
            $tmpSqls[] = $query->sql;
            $tmpBindings[] = $query->bindings;
        }

        $sql = implode($separator, $tmpSqls);
        $bindings = array_merge(...$tmpBindings);

        return new Sql($sql, $bindings);
    }

    public static function value(mixed $value): self
    {
        return new Sql('?', [$value]);
    }

    /**
     * @param array<int,mixed> $values
     */
    public static function values(array $values): self
    {
        $placeholders = array_fill(0, count($values), '?');
        $sql = '(' . implode(', ', $placeholders) . ')';
        return new Sql($sql, $values);
    }

    public static function and(Sql $lhs, Sql ...$rest): self
    {
        $tmpSql = $lhs->sql;
        $tmpBindings = [$lhs->bindings];

        foreach ($rest as $rhs) {
            $tmpSql = '(' . $tmpSql . ' AND ' . $rhs->sql . ')';
            $tmpBindings[] = $rhs->bindings;
        }

        $bindings = array_merge(...$tmpBindings);

        return new Sql($tmpSql, $bindings);
    }

    public static function or(Sql $lhs, Sql ...$rest): self
    {
        $tmpSql = $lhs->sql;
        $tmpBindings = [$lhs->bindings];

        foreach ($rest as $rhs) {
            $tmpSql = '(' . $tmpSql . ' OR ' . $rhs->sql . ')';
            $tmpBindings[] = $rhs->bindings;
        }

        $bindings = array_merge(...$tmpBindings);

        return new Sql($tmpSql, $bindings);
    }

    /**
     * @param array<int,mixed> $bindings
     */
    public function __construct(string $sql, array $bindings = [])
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    public function __toString(): string
    {
        $format = str_replace(['%', '?'], ['%%', '%s'], $this->sql);
        $args = array_map(function(mixed $binding): mixed {
            switch ($type = gettype($binding)) {
                case 'integer':
                case 'double':
                    /** @var float|int $binding */
                    return $binding;
                case 'boolean':
                    /** @var bool $binding */
                    return $binding ? 1 : 0;
                case 'NULL':
                    return 'NULL';
                case 'string':
                    /** @var string $binding */
                    $isText = mb_check_encoding($binding, 'utf-8');
                    if ($isText) {
                        return "'" . addslashes($binding) . "'";
                    } else {  // binary string
                        return "x'" . bin2hex($binding) . "'";
                    }
                    // no break
                default:
                    $typeOrClass = is_object($binding) ? get_class($binding) : $type;
                    return "'<" . $typeOrClass . ">'";
            }
        }, $this->bindings);
        return vsprintf($format, $args);
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @return array<int,mixed>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param array<int,mixed> $bindings
     */
    public function append(string $sql, array $bindings = [], string $separator = ' '): self
    {
        return new Sql(
            $this->sql . $separator . $sql,
            array_merge($this->bindings, $bindings)
        );
    }

    public function appendQuery(Sql $query, string $separator = ' '): self
    {
        return $this->append($query->sql, $query->bindings, $separator);
    }

    public function appendQueryBuilder(QueryBuilderInterface $queryBuilder, string $separator = ' '): self
    {
        $query = $queryBuilder->build();
        return $this->append('(' . $query->sql . ')', $query->bindings, $separator);
    }

    /**
     * @param array<int,mixed> $bindings
     */
    public function prepend(string $sql, array $bindings = [], string $separator = ' '): self
    {
        return new Sql(
            $sql . $separator . $this->sql,
            array_merge($bindings, $this->bindings)
        );
    }

    public function prependQuery(Sql $query, string $separator = ' '): self
    {
        return $this->prepend($query->sql, $query->bindings, $separator);
    }

    public function prependQueryBuilder(QueryBuilderInterface $queryBuilder, string $separator = ' '): self
    {
        $query = $queryBuilder->build();
        return $this->prepend('(' . $query->sql . ')', $query->bindings, $separator);
    }

    public function enclosed(): self
    {
        return new Sql(
            '(' . $this->sql . ')',
            $this->bindings
        );
    }

    public function build(): self
    {
        return $this;
    }
}
