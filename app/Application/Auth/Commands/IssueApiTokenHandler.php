<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

use App\Application\Auth\Contracts\ApiTokenIssuer;
use App\Application\Auth\Contracts\UserCredentialsGateway;
use App\Application\Auth\DTO\IssuedTokenData;
use App\Application\Common\CQRS\CommandHandler;

final readonly class IssueApiTokenHandler implements CommandHandler
{
    public function __construct(
        private UserCredentialsGateway $userCredentialsGateway,
        private ApiTokenIssuer $apiTokenIssuer,
    ) {}

    public function handle(IssueApiTokenCommand $issueApiTokenCommand): IssuedTokenData
    {
        $userIdentity = $this->userCredentialsGateway->authenticate(
            $issueApiTokenCommand->email,
            $issueApiTokenCommand->password,
        );

        return $this->apiTokenIssuer->issueFor($userIdentity, $issueApiTokenCommand->deviceName);
    }
}
