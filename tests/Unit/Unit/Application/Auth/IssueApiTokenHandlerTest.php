<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Application\Auth;

use App\Application\Auth\Commands\IssueApiTokenCommand;
use App\Application\Auth\Commands\IssueApiTokenHandler;
use App\Application\Auth\Contracts\ApiTokenIssuer;
use App\Application\Auth\Contracts\UserCredentialsGateway;
use App\Application\Auth\DTO\IssuedTokenData;
use App\Application\Auth\Exceptions\InvalidCredentials;
use App\Domain\Auth\Entities\UserIdentity;
use App\Domain\Common\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

final class IssueApiTokenHandlerTest extends TestCase
{
    public function test_it_issues_a_token_for_authenticated_user(): void
    {
        $userIdentity = new UserIdentity(
            id: UserId::fromInt(1),
            name: 'Alice',
            email: 'alice@example.com',
        );
        $issuedTokenData = new IssuedTokenData(
            token: 'plain-text-token',
            tokenType: 'Bearer',
            expiresIn: 3600,
            expiresAt: '2026-03-24T12:00:00+00:00',
        );
        $gateway = $this->createMock(UserCredentialsGateway::class);
        $issuer = $this->createMock(ApiTokenIssuer::class);

        $gateway->expects($this->once())
            ->method('authenticate')
            ->with('alice@example.com', 'password')
            ->willReturn($userIdentity);

        $issuer->expects($this->once())
            ->method('issueFor')
            ->with($userIdentity, 'Postman')
            ->willReturn($issuedTokenData);

        $issueApiTokenHandler = new IssueApiTokenHandler($gateway, $issuer);

        $result = $issueApiTokenHandler->handle(new IssueApiTokenCommand(
            email: 'alice@example.com',
            password: 'password',
            deviceName: 'Postman',
        ));

        $this->assertSame($issuedTokenData, $result);
    }

    public function test_it_propagates_invalid_credentials_from_the_gateway(): void
    {
        $gateway = $this->createMock(UserCredentialsGateway::class);
        $issuer = $this->createMock(ApiTokenIssuer::class);
        $invalidCredentials = InvalidCredentials::forEmail('alice@example.com');

        $gateway->expects($this->once())
            ->method('authenticate')
            ->with('alice@example.com', 'wrong-password')
            ->willThrowException($invalidCredentials);

        $issuer->expects($this->never())
            ->method('issueFor');

        $issueApiTokenHandler = new IssueApiTokenHandler($gateway, $issuer);

        $this->expectExceptionObject($invalidCredentials);

        $issueApiTokenHandler->handle(new IssueApiTokenCommand(
            email: 'alice@example.com',
            password: 'wrong-password',
            deviceName: 'Postman',
        ));
    }
}
