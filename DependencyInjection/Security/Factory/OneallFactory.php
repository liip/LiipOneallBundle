<?php

namespace Liip\OneallBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;

class OneallFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->addOption('create_user_if_not_exists', false);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'liip_oneall';
    }

    protected function getListenerId()
    {
        return 'liip_oneall.security.authentication.listener';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $authProviderId = 'liip_oneall.auth.'.$id;

        $definition = $container
            ->setDefinition($authProviderId, new DefinitionDecorator('liip_oneall.auth'))
            ->replaceArgument(0, $id)
        ;

        // with user provider
        if (isset($config['provider'])) {
            $definition
                ->addArgument(new Reference($userProviderId))
                ->addArgument(new Reference('security.user_checker'))
                ->addArgument($config['create_user_if_not_exists'])
            ;
        }

        return $authProviderId;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {
        $entryPointId = 'liip_oneall.security.authentication.entry_point.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('liip_oneall.security.authentication.entry_point'))
            ->replaceArgument(1, $config)
        ;

        // set options to container for use by other classes
        $container->setParameter('liip_oneall.options.'.$id, $config);

        return $entryPointId;
    }
}
