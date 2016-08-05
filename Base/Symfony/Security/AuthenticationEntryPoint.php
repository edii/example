<?php

namespace Araneum\Base\Symfony\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Class AuthenticationEntryPoint
 *
 * @package Araneum\Base\Symfony\Security
 */
class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * Starts the authentication scheme.
     *
     * @param Request                 $request       The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     *
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(
            [
                'Authorized' => false,
                'Request' => $request->getQueryString(),
                'AuthException' => $authException->getMessage(),
            ],
            401
        );
    }
}
