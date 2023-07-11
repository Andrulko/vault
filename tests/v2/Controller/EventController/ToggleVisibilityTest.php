<?php

namespace App\Tests\v2\Controller\EventController;

use App\DataFixtures\v2\BeneficiaryFixture;
use App\DataFixtures\v2\MemberFixture;
use App\Tests\Factory\BeneficiaireFactory;
use App\Tests\Factory\EventFactory;
use App\Tests\v2\Controller\AbstractControllerTest;
use App\Tests\v2\Controller\TestRouteInterface;

class ToggleVisibilityTest extends AbstractControllerTest implements TestRouteInterface
{
    private const URL = '/event/%s/toggle-visibility';

    public function provideTestRoute(): ?\Generator
    {
        yield 'Should redirect to login when not authenticated' => [self::URL, 302, null, '/login', 'PATCH'];
        yield 'Should return 200 status code when authenticated as beneficiaire' => [self::URL, 200, BeneficiaryFixture::BENEFICIARY_MAIL, null, 'PATCH'];
        yield 'Should return 200 status when authenticated as member with relay in common' => [self::URL, 200, MemberFixture::MEMBER_MAIL_WITH_RELAYS_SHARED_WITH_BENEFICIARIES, null, 'PATCH'];
        yield 'Should return 403 status code when authenticated as an other beneficiaire' => [self::URL, 403, BeneficiaryFixture::BENEFICIARY_MAIL_SETTINGS, null, 'PATCH'];
        yield 'Should return 403 status code when authenticated as member with no relay in common' => [self::URL, 403, MemberFixture::MEMBER_MAIL, null, 'PATCH'];
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
        $beneficiary = BeneficiaireFactory::findByEmail(BeneficiaryFixture::BENEFICIARY_MAIL)->object();
        $publicEvent = EventFactory::findOrCreate(['beneficiaire' => $beneficiary, 'bPrive' => false])->object();
        $url = sprintf($url, $publicEvent->getId());
        $this->assertRoute($url, $expectedStatusCode, $userMail, $expectedRedirect, $method, true);

        // Also check that authorized Pro can't update private data
        if (MemberFixture::MEMBER_MAIL_WITH_RELAYS_SHARED_WITH_BENEFICIARIES === $userMail) {
            $privateEvent = EventFactory::findOrCreate(['beneficiaire' => $beneficiary, 'bPrive' => true])->object();
            $newUrl = sprintf(self::URL, $privateEvent->getId());
            $this->assertRoute($newUrl, 403, $userMail, null, $method, true);
        }
    }
}
