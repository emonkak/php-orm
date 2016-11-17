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
    public function __construct($sql, array $bindings = [])
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
     * @return Sql
     */
    public function append($sql, array $bindings = [])
    {
        return new Sql(
            $this->sql . ' ' . $sql,
            array_merge($this->bindings, $bindings)
        );
    }

    /**
     * @param Sql $sql
     * @return Sql
     */
    public function appendSql(Sql $query)
    {
        return $this->append($query->getSql(), $query->getBindings());
    }

    /**
     * @param QueryBuilderInterface $builder
     * @return Sql
     */
    public function appendBuilder(QueryBuilderInterface $builder)
    {
        $query = $builder->build();
        return $this->append('(' . $query->getSql() . ')', $query->getBindings());
    }

    /**
     * @param string  $sql
     * @param mixed[] $bindings
     */
    public function prepend($sql, array $bindings = [])
    {
        return new Sql(
            $sql . ' ' . $this->sql,
            array_merge($bindings, $this->bindings)
        );
    }

    /**
     * @param Sql $sql
     * @return Sql
     */
    public function prependSql(Sql $query)
    {
        return $this->prepend($query->getSql(), $query->getBindings());
    }

    /**
     * @param QueryBuilderInterface $builder
     * @return Sql
     */
    public function prependBuilder(QueryBuilderInterface $builder)
    {
        $query = $builder->build();
        return $this->prepend('(' . $query->getSql() . ')', $query->getBindings());
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
