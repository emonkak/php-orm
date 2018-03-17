<?php

namespace Emonkak\Orm\Grammar;

final class GrammarProvider
{
    /**
     * @var GrammarInterface
     */
    private static $instance;

    /**
     * @return GrammarInterface
     */
    public static function get()
    {
        if (!isset(self::$instance)) {
            self::$instance = new MySqlGrammar();
        }

        return self::$instance;
    }

    /**
     * @param GrammarInterface $grammar
     */
    public static function set(GrammarInterface $grammar)
    {
        self::$instance = $grammar;
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
