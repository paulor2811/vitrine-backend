<?php

namespace App\OAuth;

use App\Models\User;
use Laravel\Passport\Bridge\AccessTokenRepository as BaseAccessTokenRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class CustomAccessTokenRepository extends BaseAccessTokenRepository
{
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        int|string|null $userIdentifier = null,
    ): CustomAccessToken {
        $token = new CustomAccessToken();
        $token->setClient($clientEntity);

        if ($userIdentifier !== null) {
            $token->setUserIdentifier($userIdentifier);
            $user = User::find($userIdentifier);
            $token->setIsAdmin((bool) ($user?->is_admin ?? false));
        }

        foreach ($scopes as $scope) {
            $token->addScope($scope);
        }

        return $token;
    }
}
