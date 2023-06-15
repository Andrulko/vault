<?php

namespace App\Tests\v2\Controller;

use App\DataFixtures\v2\BeneficiaryFixture;
use App\DataFixtures\v2\MemberFixture;

class ProControllerTest extends AbstractControllerTest implements TestRouteInterface
{
    private const HOME_URL = '/pro/create/home';
    private const SEARCH_URL = '/pro/search';

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown();
    }

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
        yield 'Should redirect to login when not authenticated' => [self::HOME_URL, 302, null, '/login'];
        yield 'Should return 200 status code when authenticated as member' => [self::HOME_URL, 200, MemberFixture::MEMBER_MAIL_WITH_RELAYS];
        yield 'Should return 403 status code when authenticated as member without permissions' => [self::HOME_URL, 403, BeneficiaryFixture::BENEFICIARY_MAIL];
        yield 'Should return 403 status code when authenticated as beneficiary' => [self::HOME_URL, 403, MemberFixture::MEMBER_MAIL];
    }

    /**  @dataProvider provideTestFormIsValid */
    public function testFormIsValid(string $url, string $formSubmit, array $values, ?string $email, ?string $redirectUrl): void
    {
        $this->assertFormIsValid($url, $formSubmit, $values, $email, $redirectUrl);
    }

    public function provideTestFormIsValid(): ?\Generator
    {
        yield 'Home submit search should redirect to search and set the url param' => [
            self::HOME_URL,
            'search',
            ['search[search]' => 'gollum'],
            MemberFixture::MEMBER_MAIL_WITH_RELAYS,
            sprintf('%s?q=%s', self::SEARCH_URL, 'gollum'),
        ];
        yield 'Search submit search should redirect to search' => [
            self::SEARCH_URL,
            'search',
            ['search[search]' => 'Gandalf'],
            MemberFixture::MEMBER_MAIL_WITH_RELAYS,
            sprintf('%s?q=%s', self::SEARCH_URL, 'Gandalf'),
        ];
    }
}
