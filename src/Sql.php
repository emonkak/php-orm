<?php

declare(strict_types=1);

namespace Emonkak\Orm;

class Sql implements QueryBuilderInterface
{
    use Explainable;
    use Fetchable;
    use Preparable;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var array<int,?scalar>
     */
    private $bindings;

    /**
     * @suppress PhanTypeExpectedObjectPropAccess
     */
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

    /**
     * @param ?scalar $value
     */
    public static function value($value): self
    {
        return new Sql('?', [$value]);
    }

    /**
     * @param array<int,?scalar> $values
     */
    public static function values(array $values): self
    {
        $placeholders = array_fill(0, count($values), '?');
        $sql = '(' . implode(', ', $placeholders) . ')';
        return new Sql($sql, $values);
    }

    public static function _and(Sql $lhs, Sql ...$rest): self
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

    public static function _or(Sql $lhs, Sql ...$rest): self
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
     * @param array<int,?scalar> $bindings
     */
    public function __construct(string $sql, array $bindings = [])
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    public function __toString(): string
    {
        $format = str_replace(['%', '?'], ['%%', '%s'], $this->sql);
        $args = array_map(function($binding) {
            switch ($type = gettype($binding)) {
            case 'integer':
            case 'double':
                return $binding;
            case 'boolean':
                return $binding ? 1 : 0;
            case 'NULL':
                return 'NULL';
            case 'string':
                /** @psalm-var string $binding */
                $isText = mb_check_encoding($binding, 'utf-8');  // @phan-suppress-current-line PhanTypeMismatchArgumentNullableInternal
                if ($isText) {
                    return "'" . addslashes($binding) . "'";  // @phan-suppress-current-line PhanTypeMismatchArgumentNullableInternal
                } else {  // binary string
                    return "x'" . bin2hex($binding) . "'";  // @phan-suppress-current-line PhanTypeMismatchArgumentNullableInternal
                }
            default:
                /** @psalm-var mixed $binding */
                $typeOrClass = is_object($binding) ? get_class($binding) : $type;  // @phan-suppress-current-line PhanTypeMismatchArgumentInternal
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
     * @return array<int,?scalar>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param array<int,?scalar> $bindings
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
     * @param array<int,?scalar> $bindings
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
