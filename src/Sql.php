<?php

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
     * @var mixed
     */
    private $bindings;

    /**
     * @param string $format
     * @param Sql[]  ...$args
     * @return Sql
     */
    public static function format($format /** , ...$args */)
    {
        $args = array_slice(func_get_args(), 1);
        $tmpSqls = [];
        $tmpBindings = [];
        foreach ($args as $arg) {
            $tmpSqls[] = $arg->getSql();
            $tmpBindings[] = $arg->getBindings();
        }
        $sql = vsprintf($format, $tmpSqls);
        $bindings = call_user_func_array('array_merge', $tmpBindings);
        return new Sql($sql, $bindings);
    }

    /**
     * @param string $string
     * @return Sql
     */
    public static function literal($string)
    {
        return new Sql($string, []);
    }

    /**
     * @param mixed $value
     * @return Sql
     */
    public static function value($value)
    {
        return new Sql('?', [$value]);
    }

    /**
     * @param mixed[] $values
     * @return Sql
     */
    public static function values(array $values)
    {
        $placeholders = array_fill(0, count($values), '?');
        $sql = '(' . implode(', ', $placeholders) . ')';
        return new Sql($sql, $values);
    }

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     */
    public function __construct($sql, array $bindings)
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $format = str_replace(['%', '?'], ['%%', '%s'], $this->sql);
        $args = array_map(function($binding) {
            switch (gettype($binding)) {
            case 'integer':
            case 'double':
                return $binding;
            case 'boolean':
                return $binding ? 1 : 0;
            case 'NULL':
                return 'NULL';
            default:
                if (mb_check_encoding($binding, 'utf-8')) {
                    return "'" . addslashes($binding) . "'";
                } else {  // binary string
                    return "x'" . bin2hex($binding) . "'";
                }
                break;
            }
        }, $this->bindings);
        return vsprintf($format, $args);
    }

    /**
     * {@inheritDoc}
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * {@inheritDoc}
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     * @param string  $separator
     * @return Sql
     */
    public function append($sql, array $bindings = [], $separator = ' ')
    {
        return new Sql(
            $this->sql . $separator . $sql,
            array_merge($this->bindings, $bindings)
        );
    }

    /**
     * @param Sql    $sql
     * @param string $separator
     * @return Sql
     */
    public function appendSql(Sql $query, $separator = ' ')
    {
        return $this->append($query->getSql(), $query->getBindings(), $separator);
    }

    /**
     * @param QueryBuilderInterface $builder
     * @param string                $separator
     * @return Sql
     */
    public function appendBuilder(QueryBuilderInterface $builder, $separator = ' ')
    {
        $query = $builder->build();
        return $this->append('(' . $query->getSql() . ')', $query->getBindings(), $separator);
    }

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     * @param string  $separator
     */
    public function prepend($sql, array $bindings = [], $separator = ' ')
    {
        return new Sql(
            $sql . $separator . $this->sql,
            array_merge($bindings, $this->bindings)
        );
    }

    /**
     * @param Sql    $sql
     * @param string $separator
     * @return Sql
     */
    public function prependSql(Sql $query, $separator = ' ')
    {
        return $this->prepend($query->getSql(), $query->getBindings(), $separator);
    }

    /**
     * @param QueryBuilderInterface $builder
     * @param string                $separator
     * @return Sql
     */
    public function prependBuilder(QueryBuilderInterface $builder, $separator = ' ')
    {
        $query = $builder->build();
        return $this->prepend('(' . $query->getSql() . ')', $query->getBindings(), $separator);
    }

    /**
     * @return Sql
     */
    public function enclosed()
    {
        return new Sql(
            '(' . $this->sql . ')',
            $this->bindings
        );
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this;
    }
}
