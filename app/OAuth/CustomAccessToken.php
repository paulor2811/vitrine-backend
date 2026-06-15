<?php

namespace App\OAuth;

use DateTimeImmutable;
use League\OAuth2\Server\CryptKeyInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class CustomAccessToken implements AccessTokenEntityInterface
{
    use EntityTrait, AccessTokenTrait;

    private ClientEntityInterface $client;
    private array $scopes = [];
    private DateTimeImmutable $expiryDateTime;
    private ?string $userIdentifier = null;
    private bool $isAdmin = false;

    public function setIsAdmin(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
    }

    public function getClient(): ClientEntityInterface
    {
        return $this->client;
    }

    public function setClient(ClientEntityInterface $client): void
    {
        $this->client = $client;
    }

    public function getExpiryDateTime(): DateTimeImmutable
    {
        return $this->expiryDateTime;
    }

    public function setExpiryDateTime(DateTimeImmutable $dateTime): void
    {
        $this->expiryDateTime = $dateTime;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(int|string $identifier): void
    {
        $this->userIdentifier = (string) $identifier;
    }

    public function addScope(ScopeEntityInterface $scope): void
    {
        $this->scopes[$scope->getIdentifier()] = $scope;
    }

    public function getScopes(): array
    {
        return array_values($this->scopes);
    }

    // Sobrescreve toString() (public no trait) para injetar is_admin no JWT.
    // convertToJWT() é private no trait e não pode ser sobrescrito.
    public function toString(): string
    {
        $this->initJwtConfiguration();

        $subject = $this->getUserIdentifier() ?? $this->getClient()->getIdentifier();

        return $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo($subject)
            ->withClaim('scopes', $this->getScopes())
            ->withClaim('is_admin', $this->isAdmin)
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey())
            ->toString();
    }
}
