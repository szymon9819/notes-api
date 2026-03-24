<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Application\Auth\Commands\IssueApiTokenCommand;
use App\Application\Auth\DTO\IssuedTokenData;
use App\Application\Auth\Exceptions\InvalidCredentials;
use App\Application\Common\CQRS\CommandBus;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\IssueApiTokenRequest;
use App\Infrastructure\Presentation\Api\IssuedTokenPresenter;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

#[Group('Authentication', 'Authentication endpoints for the demo API.', 5)]
class AuthTokenController extends Controller
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly IssuedTokenPresenter $issuedTokenPresenter,
    ) {}

    #[Endpoint('login', 'Login', 'Authenticates the user and returns a Sanctum bearer token with TTL.')]
    public function login(IssueApiTokenRequest $issueApiTokenRequest): JsonResponse
    {
        try {
            $issuedToken = $this->commandBus->dispatch(new IssueApiTokenCommand(
                email: $issueApiTokenRequest->email(),
                password: $issueApiTokenRequest->password(),
                deviceName: $issueApiTokenRequest->deviceName(),
            ));
        } catch (InvalidCredentials) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$issuedToken instanceof IssuedTokenData) {
            abort(500);
        }

        return response()->json($this->issuedTokenPresenter->present($issuedToken));
    }
}
