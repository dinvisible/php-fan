<?php

declare(strict_types=1);
use fan\core\view\definer;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../_core/base/expression_evaluator.php';
require_once __DIR__ . '/../../../_core/view/definer.php';

class DefinerTest extends TestCase
{
    public function testGetViewParserNameEvaluatesConfiguredRulesWithoutEval(): void
    {
        $definer = new definer(
            [
                'default_format' => 'html',
                'rule' => [
                    'loader' => [
                        'PG.dl_ctrl.b.1',
                        '(APG.format.s.1)',
                    ],
                    'json' => [
                        '(H.X-Requested-With.s.0)&&(APG.format.s.2||APG.format.s.3)',
                    ],
                ],
                'value' => [
                    0 => 'XMLHttpRequest',
                    1 => 'loader',
                    2 => 'json',
                    3 => 'JSON',
                ],
            ],
            new FanTestDefinerRequest([
                'PG' => ['dl_ctrl' => false],
                'APG' => ['format' => 'json'],
                'H' => ['X-Requested-With' => 'XMLHttpRequest'],
            ])
        );

        $this->assertSame('json', $definer->getViewParserName());
    }

    public function testGetViewParserNameSupportsNumericIntegerAndRegexpRules(): void
    {
        $definer = new definer(
            [
                'default_format' => 'html',
                'rule' => [
                    'api' => [
                        'APG.count.n.0&&APG.id.i.42&&APG.path.r.1',
                    ],
                ],
                'value' => [
                    0 => '>=10',
                    1 => '/^\\/api\\//',
                ],
            ],
            new FanTestDefinerRequest([
                'APG' => [
                    'count' => '11',
                    'id' => '42',
                    'path' => '/api/users',
                ],
            ])
        );

        $this->assertSame('api', $definer->getViewParserName());
    }

    public function testGetViewParserNameUsesInjectedTabForDefaultFormat(): void
    {
        $definer = new definer(
            ['default_format' => 'html'],
            new FanTestDefinerRequest([]),
            new FanTestDefinerTab('xml')
        );

        $this->assertSame('xml', $definer->getViewParserName());
    }
}

class FanTestDefinerRequest
{
    public function __construct(private array $data)
    {
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function get(string $key, string $source, mixed $default = null): mixed
    {
        return $this->data[$source][$key] ?? $default;
    }
}

class FanTestDefinerTab
{
    public function __construct(private string $format)
    {
    }

    public function getTabMeta(string $key, mixed $default = null): mixed
    {
        return $key === 'default_view_format' ? $this->format : $default;
    }
}
