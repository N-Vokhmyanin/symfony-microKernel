<?php

namespace Core\Security\Authenticators;

use Core\Repository\Entities\UserRepository;
use Core\Security\Authenticators\Badges\FutureFunctionalBadge;
use Core\Services\Helpers\PlugLogger;
use Core\Services\Helpers\Response\ResponseData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class UserHeaderAuthenticator implements AuthenticatorInterface
{
    private const HEADER_USER_ID_NAME = 'Example-User-Id';

    private UserRepository $userRepository;
    private ContainerInterface $container;

    public function __construct(UserRepository $ur, ContainerInterface $container)
    {
        $this->userRepository = $ur;
        $this->container = $container;
    }

    public function supports(Request $request): ?bool
    {
        // Делаем только для запросов, у которых есть Example-User-Id заголовок
        return $request->headers->has(self::HEADER_USER_ID_NAME);
    }

    public function authenticate(Request $request): Passport
    {
        return new SelfValidatingPassport(new UserBadge($request->headers->get(self::HEADER_USER_ID_NAME, 0), function ($userId) {
            $iUserId = (int)$userId;

            if ($iUserId === 0) {
                return null;
            }

            return $this->userRepository->loadUserByIdentifier($iUserId);
        }),
        [
            new FutureFunctionalBadge($this->container->getParameter('feature_functional_actions'), $request)
        ]);
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        // Токен важен для того, чтобы дальше после процедуры аутентификации можно было получить объект текущего пользователя в системе.
        return new RememberMeToken($passport->getUser(), $firewallName, $passport->getUser()->getUserIdentifier());
    }

    public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface
    {
        /** @noinspection PhpParamsInspection (Deprecated createAuthenticatedToken method) */
        return $this->createToken($passport, $firewallName);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // let the original request continue
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return (new ResponseData(new PlugLogger, null, Response::HTTP_UNAUTHORIZED))->setCustomError([
            'message' => 'User not auth',
            'code' => Response::HTTP_UNAUTHORIZED
        ])->getJsonResponse();
    }
}