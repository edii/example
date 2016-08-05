<?php

namespace Araneum\Base\Symfony\Security;

use Araneum\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * Class AuthenticationSuccessHandler
 *
 * @package Araneum\Base\Symfony\Security
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @inheritdoc
     * @param Request        $request
     * @param TokenInterface $token
     * @return JsonResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        return new JsonResponse(
            [
                'name' => $user->getFullName(),
                'email' => $user->getEmail(),
            ]
        );
    }
}
