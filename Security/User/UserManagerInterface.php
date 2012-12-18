<?php

namespace Liip\OneallBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;

interface UserManagerInterface extends UserProviderInterface
{
    function createUserFromUid($uid);
}