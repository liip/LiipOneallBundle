<?php

namespace Liip\OneallBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class OneallExtension extends \Twig_Extension
{
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            'oneall_initialize' => new \Twig_Function_Method($this, 'renderInitialize', array('is_safe' => array('html'))),
            'oneall_login_button' => new \Twig_Function_Method($this, 'renderLoginButton', array('is_safe' => array('html'))),
            'oneall_logout_url' => new \Twig_Function_Method($this, 'renderLogoutUrl', array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'oneall';
    }

    public function renderInitialize($parameters = array(), $name = null)
    {
        $name = $name ?: 'LiipOneallBundle::initialize.html.twig';
        return $this->container->get('templating')->render($name, $parameters + array(
            'app_url' => $this->container->getParameter('liip_oneall.app_url'),
            'site_subdomain' => $this->container->getParameter('liip_oneall.site_subdomain'),
        ));
    }

    public function renderLoginButton($parameters = array(), $name = null)
    {
        $name = $name ?: 'LiipOneallBundle::loginButton.html.twig';
        return $this->container->get('templating')->render($name, $parameters + array(
            'login_container_id' => 'oa_social_login_container',
            'app_url' => $this->container->getParameter('liip_oneall.app_url'),
            'callback_uri' => $this->getCallbackUri(),
            'social_links' => $this->container->getParameter('liip_oneall.social_links'),
        ));
    }

    public function renderLogoutUrl($parameters = array(), $name = null)
    {
        return $this->container->get('oneall')->getLogoutUrl($parameters);
    }

    private function getCallbackUri()
    {
        $callbackPath = $this->container->getParameter('liip_oneall.callback_path');
        if (!$callbackPath) {
            $firewallName = isset($parameters['firewall_name'])
                ? $parameters['firewall_name']
                : $this->container->getParameter('liip_oneall.default_firewall_name');
            $firewallOptions = $this->container->getParameter('liip_oneall.options.'.$firewallName);
            $callbackPath = $firewallOptions['check_path'];
        }

        return '/' === $callbackPath[0]
            ? $this->container->get('request')->getSchemeAndHttpHost().$callbackPath
            : $this->container->get('router')->generate($callbackPath, array(), true)
        ;
    }
}
