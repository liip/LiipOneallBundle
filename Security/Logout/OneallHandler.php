<?php

namespace Liip\OneallBundle\Security\Logout;

use Symfony\Component\HttpFoundation\Request;
use Liip\OneallBundle\Oneall\OneallApi;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Listener for the logout action
 *
 * This handler will clear the application's Oneall cookie.
 */
class OneallHandler implements LogoutHandlerInterface
{
    private $oneall;

    public function __construct(OneallApi $oneall)
    {
        $this->oneall = $oneall;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->oneall->destroySession();
    }
}
