<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../_core/base/expression_evaluator.php';
require_once __DIR__ . '/../../../../_core/service/entity/snippet.php';

class SnippetTest extends \PHPUnit\Framework\TestCase
{
    public function testSnippetConditionFiltersQueryAndPreparesData(): void
    {
        $snippet = $this->makeSnippet(
            'where name = {$name} and age >= {$age}',
            '(filled{$name}&exist{$age})&&val{$age}>=const[18]'
        );

        $this->assertSame(
            ['where name = ? and age >= ?', ['Sergey', 21]],
            $snippet->getSnippetQuery(['name' => 'Sergey', 'age' => 21])
        );

        $this->assertSame(
            ['', []],
            $snippet->getSnippetQuery(['name' => '', 'age' => 21])
        );
    }

    public function testSnippetSupportsStringConstants(): void
    {
        $snippet = $this->makeSnippet('and status = {$status}', "const['active']==val{\$status}");

        $this->assertSame(
            ['and status = ?', ['active']],
            $snippet->getSnippetQuery(['status' => 'active'])
        );
        $this->assertSame(['', []], $snippet->getSnippetQuery(['status' => 'disabled']));
    }

    public function testEmptyConditionIsTreatedAsUnconditional(): void
    {
        $snippet = $this->makeSnippet('and id = {$id}', '');

        $this->assertSame(['and id = ?', [10]], $snippet->getSnippetQuery(['id' => 10]));
    }

    private function makeSnippet(string $query, string $condition): \fan\core\service\entity\snippet
    {
        $reflection = new \ReflectionClass(\fan\core\service\entity\snippet::class);
        $snippet = $reflection->newInstanceWithoutConstructor();

        $parsedCondition = $reflection->getMethod('_parseCondition')->invoke($snippet, $condition);
        $usedKeys = $reflection->getMethod('_parsePlaceHolders')->invoke($snippet, $query);

        $reflection->getProperty('srcCondition')->setValue($snippet, $condition);
        $reflection->getProperty('condition')->setValue($snippet, $parsedCondition);
        $reflection->getProperty('usedKeys')->setValue($snippet, $usedKeys);
        $reflection->getProperty('callback')->setValue($snippet, null);

        return $snippet;
    }
}
