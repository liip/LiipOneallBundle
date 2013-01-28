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
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserProvider implements UserProviderInterface
{
    protected $oneall;
    protected $userManager;
    protected $validator;
    protected $session;

    public function __construct(OneallApi $oneall, UserManagerInterface $userManager, Validator $validator, ContainerInterface $container)
    {
        $this->oneall = $oneall;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->session = $container->get('request')->getSession();
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
            $this->session->setFlash("Userdata could not be loaded");
        }

        if (!empty($userdata)) {
            $network = array_shift($userdata['networks']);

            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setOneallId($username);
                $user->setEnabled(true);
                $user->setPassword('');
            }

            if (empty($network['email'])) {
                $network['email'] = '';
            }

            $user->setUserData($network);

            $validation = $this->validator->validate($user, 'Oneall');

            if (count($validation)) {
                $this->session->setFlash("Username could not be stored");
            }

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
