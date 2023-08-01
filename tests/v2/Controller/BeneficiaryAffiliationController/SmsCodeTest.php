<?php

namespace App\Tests\v2\Controller\BeneficiaryAffiliationController;

use App\DataFixtures\v2\BeneficiaryFixture;
use App\DataFixtures\v2\MemberFixture;
use App\Tests\Factory\BeneficiaireFactory;
use App\Tests\v2\Controller\AbstractControllerTest;
use App\Tests\v2\Controller\TestFormInterface;
use App\Tests\v2\Controller\TestRouteInterface;

class SmsCodeTest extends AbstractControllerTest implements TestRouteInterface, TestFormInterface
{
    private const URL = '/beneficiary/%s/affiliate/relays/sms_code';
    private const URL_RELAYS = '/beneficiary/%s/affiliate/relays';
    private const FORM_VALUES = [
        'relay_affiliation_sms_code[code]' => '1234',
    ];

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
        $beneficiary = BeneficiaireFactory::createOne()->object();
        $url = sprintf($url, $beneficiary->getId());
        $this->assertRoute($url, $expectedStatusCode, $userMail, $expectedRedirect, $method);
    }

    public function provideTestRoute(): ?\Generator
    {
        yield 'Should redirect to login when not authenticated' => [self::URL, 302, null, '/login'];
        yield 'Should return 200 when authenticated as professional' => [self::URL, 200, MemberFixture::MEMBER_MAIL];
        yield 'Should return 403 status code when authenticated as beneficiary' => [self::URL, 403, BeneficiaryFixture::BENEFICIARY_MAIL];
    }

    /** @dataProvider provideTestFormIsValid */
    public function testFormIsValid(string $url, string $formSubmit, array $values, ?string $email, ?string $redirectUrl): void
    {
        $beneficiary = BeneficiaireFactory::createOne(['relayInvitationSmsCode' => '1234', 'relayInvitationSmsCodeSendAt' => new \DateTime()])->object();
        $url = sprintf(self::URL, $beneficiary->getId());
        $redirectUrl = sprintf(self::URL_RELAYS, $beneficiary->getId());
        $this->assertFormIsValid($url, $formSubmit, $values, $email, $redirectUrl);
    }

    public function provideTestFormIsValid(): ?\Generator
    {
        yield 'Should redirect to relays choice when form is correct' => [
            self::URL,
            'submit',
            self::FORM_VALUES,
            MemberFixture::MEMBER_MAIL,
            self::URL_RELAYS,
        ];
    }

    /** @dataProvider provideTestFormIsNotValid */
    public function testFormIsNotValid(string $url, string $route, string $formSubmit, array $values, array $errors, ?string $email, string $alternateSelector = null): void
    {
        $beneficiary = BeneficiaireFactory::createOne(['relayInvitationSmsCode' => '1234', 'relayInvitationSmsCodeSendAt' => new \DateTime()])->object();
        $url = sprintf(self::URL, $beneficiary->getId());
        $this->assertFormIsNotValid($url, $route, $formSubmit, $values, $errors, $email, $alternateSelector);
    }

    public function provideTestFormIsNotValid(): ?\Generator
    {
        $values = self::FORM_VALUES;
        $values['relay_affiliation_sms_code[code]'] = 'wrong code';

        yield 'Should return an error when sms code is wrong' => [
            self::URL,
            'affiliate_beneficiary_relays_sms_code',
            'submit',
            $values,
            [
                [
                    'message' => 'wrong_sms_code',
                    'params' => null,
                ],
            ],
            MemberFixture::MEMBER_MAIL,
        ];
    }
}
