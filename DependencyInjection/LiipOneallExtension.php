<?php

namespace Liip\OneallBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class LiipOneallExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('oneall.xml');
        $loader->load('security.xml');

        if (isset($config['alias'])) {
            $container->setAlias($config['alias'], 'liip_oneall.api');
        }

        foreach (array('api', 'twig', 'fosuserbundle_provider') as $attribute) {
            $container->setParameter('liip_oneall.'.$attribute.'.class', $config['class'][$attribute]);
        }

        foreach (array('site_subdomain', 'site_public_key', 'site_private_key', 'social_links', 'callback_path', 'default_firewall_name') as $attribute) {
            $container->setParameter('liip_oneall.'.$attribute, $config[$attribute]);
        }

        $container->setParameter('liip_oneall.app_url', 'https://'.$config['site_subdomain'].'.api.oneall.com');

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['FOSUserBundle'])) {
            $loader->load('security.fosuserbundle.xml');
        }
    }
}
