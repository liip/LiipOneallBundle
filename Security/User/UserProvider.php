<?php

namespace Liip\OneallBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Liip\OneallBundle\Security\OneallApiException;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Model\UserManagerInterface;
use Liip\OneallBundle\Oneall\OneallApi;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider implements UserProviderInterface
{
    protected $oneall;
    protected $userManager;
    protected $validator;

    public function __construct(OneallApi $oneall, UserManagerInterface $userManager, Validator $validator)
    {
        $this->oneall = $oneall;
        $this->userManager = $userManager;
        $this->validator = $validator;
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    public function findUserByOneId($uid)
    {
        return $this->userManager->findUserBy(array('oneallId' => $uid));
    }

    public function loadUserByUsername($username)
    {
        $user = $this->findUserByOneId($username);

        try {
            $userdata = $this->oneall->getUserData($username);
        } catch (OneallApiException $e) {
            $userdata = null;
        } catch (\Exception $e) {
            var_dump($e->getMessage()); die;
        }

        if (!empty($userdata)) {
            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPassword('');
            }

            // TODO subscribe to changes, so that we would not have to do this if the user already exists
            $user->setUserData($userdata);
/*
            $validation = $this->validator->validate($user, 'Oneall');
            if (count($validation)) {
                // TODO: add flash message?
                throw new UsernameNotFoundException('Oneall user could not be stored');
            }
*/
            $this->userManager->updateUser($user);
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('Oneall user not found');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getOneallId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getOneallId());
    }
}
