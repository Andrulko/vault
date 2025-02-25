<?php

namespace App\Tests\v2\Controller\UserController;

use App\DataFixtures\v2\BeneficiaryFixture;
use App\DataFixtures\v2\MemberFixture;
use App\Tests\v2\Controller\AbstractControllerTest;
use App\Tests\v2\Controller\TestRouteInterface;

class FirstVisitTest extends AbstractControllerTest implements TestRouteInterface
{
    private const URL = '/user/first-visit';

    /** @dataProvider provideTestRoute */
    public function testRoute(
        string $url,
        int $expectedStatusCode,
        string $userMail = null,
        string $expectedRedirect = null,
        string $method = 'GET',
        bool $isXmlHttpRequest = false,
        array $body = [],
    ): void {
        $this->assertRoute($url, $expectedStatusCode, $userMail, $expectedRedirect, $method);
    }

    public function provideTestRoute(): ?\Generator
    {
        yield 'Should redirect to login when not authenticated' => [self::URL, 302, null, '/login'];
        yield 'Should return 200 status code when authenticated as member in first visit' => [self::URL, 200, MemberFixture::MEMBER_FIRST_VISIT];
        yield 'Should return 200 status code when authenticated as beneficiary in first visit' => [self::URL, 200, BeneficiaryFixture::BENEFICIARY_MAIL_FIRST_VISIT];
        yield 'Should return 302 status code when authenticated as member' => [self::URL, 302, MemberFixture::MEMBER_MAIL, '/user/redirect-user/'];
        yield 'Should return 302 status code when authenticated as beneficiary' => [self::URL, 302, BeneficiaryFixture::BENEFICIARY_MAIL, '/user/redirect-user/'];
    }
}
