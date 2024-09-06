<?php

namespace App\Services;

use App\Security\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class JwtService
{
    private JWTTokenManagerInterface $jwtManager;

    private int $ttl;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        ParameterBagInterface    $parameterBag
    )
    {
        $this->jwtManager = $jwtManager;
        $this->ttl = $parameterBag->get("lexik_jwt_authentication.token_ttl");
    }

    public function createToken(User $user): string
    {
        return $this->jwtManager->createFromPayload(
            $user,
            $user->toJwtPayload()
        );
    }

    public function getTtl(bool $isMs = false): int
    {
        return $isMs ? $this->ttl * 1000 : $this->ttl;
    }

    public function getTokenExpiration(string $jwt, bool $isMs = false): int
    {
        $exp = $this->jwtManager->parse($jwt)["exp"];
        return $isMs ? $exp * 1000 : $exp;
    }

    public function createJsonResponse(User $user): JsonResponse
    {
        $token = $this->createToken($user);
        return new JsonResponse([
            "accessToken" => [
                "value" => $token,
                "expiration" => $this->getTokenExpiration($token, true)
            ],
            "user" => $user->toArray()
        ]);
    }
}
