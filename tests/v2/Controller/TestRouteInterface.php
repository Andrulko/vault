<?php

namespace App\Tests\v2\Controller;

interface TestRouteInterface
{
    /** @dataProvider provideTestRoute */
    public function testRoute(string $url, int $expectedStatusCode, ?string $userMail = null, ?string $expectedRedirect = null, string $method = 'GET'): void;

    public function provideTestRoute(): ?\Generator;
}
