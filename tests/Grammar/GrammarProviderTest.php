<?php

namespace Emonkak\Orm\Tests\Grammar;

use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Grammar\GrammarProvider;
use Emonkak\Orm\Grammar\MySqlGrammar;

/**
 * @runTestsInSeparateProcesses
 */
class GrammarProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $this->assertInstanceOf(MySqlGrammar::class, GrammarProvider::get());
        $this->assertSame(GrammarProvider::get(), GrammarProvider::get());
    }

    public function testSet()
    {
        $defaultGrammar = GrammarProvider::get();
        $mockedGrammar = $this->createMock(GrammarInterface::class);

        GrammarProvider::set($mockedGrammar);

        $this->assertSame($mockedGrammar, GrammarProvider::get());

        GrammarProvider::set($defaultGrammar);

        $this->assertSame($defaultGrammar, GrammarProvider::get());
    }
}
