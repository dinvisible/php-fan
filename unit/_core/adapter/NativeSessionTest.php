<?php

declare(strict_types=1);

use fan\core\adapter\native_session;
use PHPUnit\Framework\TestCase;

final class NativeSessionTest extends TestCase
{
    public function testAdapterWrapsSafeNativeSessionQueries(): void
    {
        $session = new native_session();

        $this->assertIsInt($session->status());
        $this->assertIsString($session->id());
        $this->assertIsString($session->name());
    }
}
