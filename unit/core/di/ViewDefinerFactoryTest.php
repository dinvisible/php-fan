<?php

declare(strict_types=1);

use fan\core\di\view_definer_factory;
use PHPUnit\Framework\TestCase;
use fan\core\view\definer;

final class ViewDefinerFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectViewDefiner(): void
    {
        require_once dirname(__DIR__, 3) . '/core/view/definer.php';

        if (!class_exists('\fan\project\view\definer', false)) {
            class_alias(definer::class, '\fan\project\view\definer');
        }

        $request = new stdClass();
        $tab = new ViewDefinerFactoryTabDouble();
        $definer = (new view_definer_factory())(['default_format' => 'json'], $request, $tab);

        $this->assertInstanceOf(definer::class, $definer);
        $this->assertSame('json', $definer->getViewParserName());
    }}

final class ViewDefinerFactoryTabDouble
{
    public function getTabMeta(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}
