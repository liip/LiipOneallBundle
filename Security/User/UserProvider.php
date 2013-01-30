<?php

namespace Liip\OneallBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator;

use FOS\UserBundle\Model\UserManagerInterface;

use Liip\OneallBundle\Security\OneallApiException;
use Liip\OneallBundle\Oneall\OneallApi;

class UserProvider implements UserProviderInterface
{
    protected $oneall;
    protected $userManager;
    protected $validator;
    protected $container;
    protected $validationGroups;

    public function __construct(OneallApi $oneall, UserManagerInterface $userManager, Validator $validator, ContainerInterface $container)
    {
        $this->oneall = $oneall;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->container = $container;
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    public function findUserByOneAllId($uid)
    {
        return $this->userManager->findUserBy(array('oneallId' => $uid));
    }

    public function loadUserByUsername($username)
    {
        $user = $this->findUserByOneAllId($username);

        try {
            $userdata = $this->oneall->getUserData($username);
        } catch (OneallApiException $e) {
            $userdata = null;
        } catch (\Exception $e) {
            $this->container->get('request')->getSession()->setFlash("oneall_user_error", "Could not retrieve user data.");
        }

        if (!empty($userdata)) {
            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPassword('');
            }
            $user->setOneallId($username);

            if (!$this->updateUser($user, $userdata)) {
                $this->container->get('request')->getSession()->setFlash("oneall_user_error", "Username could not be stored.");
            }
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('Oneall user not found.');
        }

        return $user;
    }

    protected function updateUser($user, $userdata)
    {
        $network = array_shift($userdata['networks']);

        if (empty($network['email'])) {
            $network['email'] = '';
        }

        $user->setUserData($network);

        $validation = $this->validator->validate($user, $this->validationGroups);
        if (count($validation)) {
            return false;
        }

        $this->userManager->updateUser($user);

        return true;
    }

    public function refreshUser(UserInterface $user)
    {
        $user = $this->userManager->findUserBy(array('id' => $user->getId()));
        if (!$this->supportsClass(get_class($user)) || !$user->getOneallId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getOneallId());
    }
}
