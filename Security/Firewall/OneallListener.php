<?php

/*
 * This file is part of the LiipOneallBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\OneallBundle\Security\Firewall;

use Liip\OneallBundle\Security\Authentication\Token\OneallUserToken;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * Oneall authentication listener.
 */
class OneallListener extends AbstractAuthenticationListener
{
    protected function attemptAuthentication(Request $request)
    {
        return $this->authenticationManager->authenticate(new OneallUserToken($this->providerKey));
    }
}
