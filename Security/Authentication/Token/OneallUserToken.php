<?php

namespace Liip\OneallBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OneallUserToken extends AbstractToken
{
    private $providerKey;

    public function __construct($providerKey, $uid = '', array $roles = array())
    {
        parent::__construct($roles);

        $this->setUser($uid);

        if (!empty($uid)) {
            $this->setAuthenticated(true);
        }

        $this->providerKey = $providerKey;
    }

    public function getCredentials()
    {
        return '';
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function serialize()
    {
        return serialize(array($this->providerKey, parent::serialize()));
    }

    public function unserialize($str)
    {
        list($this->providerKey, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
}
