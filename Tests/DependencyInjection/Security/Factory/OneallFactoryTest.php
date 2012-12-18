<?php

namespace Liip\OneallBundle\Tests\DependencyInjection\Security\Factory;

use Liip\OneallBundle\DependencyInjection\Security\Factory\OneallFactory;

class OneallFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Liip\OneallBundle\DependencyInjection\Security\Factory\OneallFactory
     */
    private $factory = null;

    public function setUp()
    {
        $this->factory = new OneallFactory();
    }

    public function testThatCanGetPosition()
    {
        $this->assertEquals('pre_auth', $this->factory->getPosition());
    }

    public function testThatCanGetKey()
    {
        $this->assertEquals('liip_oneall', $this->factory->getKey());
    }

    /**
     * @covers Liip\OneallBundle\DependencyInjection\Security\Factory\OneallFactory::createAuthProvider
     */
    public function testThatCreateUserAuthProviderWhenDefinedInConfig()
    {
        $idsArray = $this->oneallFactoryCreate(array('provider' => true, 'remember_me' => false, 'create_user_if_not_exists' => false));
        $this->assertEquals('liip_oneall.auth.l3l0', $idsArray[0]);
    }

    /**
     * @covers Liip\OneallBundle\DependencyInjection\Security\Factory\OneallFactory::createAuthProvider
     */
    public function testThatCreateUserAuthProviderEvenWhenNotDefinedInConfig()
    {
        $idsArray = $this->oneallFactoryCreate(array('remember_me' => false));
        $this->assertEquals('liip_oneall.auth.l3l0', $idsArray[0]);
    }

    /**
     * @covers Liip\OneallBundle\DependencyInjection\Security\Factory\OneallFactory::createAuthProvider
     */
    public function testThatCreateDifferentUserAuthProviderForDifferentFirewalls()
    {
        $idsArray = $this->oneallFactoryCreate(array('remember_me' => false));
        $this->assertEquals('liip_oneall.auth.l3l0', $idsArray[0]);

        $idsArray = $this->oneallFactoryCreate(array('remember_me' => false), 'main');
        $this->assertEquals('liip_oneall.auth.main', $idsArray[0]);
    }

    /**
     * @covers Liip\OneallBundle\DependencyInjection\Security\Factory\OneallFactory::createEntryPoint
     */
    public function testThatCreateEntryPoint()
    {
        $idsArray = $this->oneallFactoryCreate(array('remember_me' => false));
        $this->assertEquals('liip_oneall.security.authentication.entry_point.l3l0', $idsArray[2]);
    }

    /**
     * @covers Liip\OneallBundle\DependencyInjection\Security\Factory\OneallFactory::getListenerId
     */
    public function testThatListenerForListenerId()
    {
        $idsArray = $this->oneallFactoryCreate(array('remember_me' => false));
        $this->assertEquals('liip_oneall.security.authentication.listener.l3l0', $idsArray[1]);
    }

    /**
     * @param array $config
     * @return array
     */
    private function oneallFactoryCreate($config = array(), $id = 'l3l0')
    {
        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition', array('addArgument', 'replaceArgument'));
        $definition->expects($this->any())
            ->method('replaceArgument')
            ->will($this->returnValue($definition));
        $definition->expects($this->any())
            ->method('addArgument')
            ->will($this->returnValue($definition));
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', array('setDefinition'));
        $container->expects($this->any())
            ->method('setDefinition')
            ->will($this->returnValue($definition));

        return $this->factory->create($container, $id, $config, 'l3l0.user.provider', null);
    }
}
