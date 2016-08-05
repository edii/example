<?php

namespace Araneum\Base\Symfony\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Class AuthenticationSuccessHandler
 *
 * @package Araneum\Base\Symfony\Security
 */
class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * @var CsrfTokenManager
     */
    private $tokenManager;

    /**
     * AuthenticationFailureHandler constructor.
     *
     * @param CsrfTokenManager $tokenManager
     */
    public function __construct(CsrfTokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse The response to return, never null
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(
            $this->tokenManager
                ->getToken('authenticate')
                ->getValue(),
            Response::HTTP_BAD_REQUEST
        );
    }
}
