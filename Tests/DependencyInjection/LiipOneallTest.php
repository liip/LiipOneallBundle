<?php

namespace Liip\OneallBundle\Tests\DependencyInjection;

use Liip\OneallBundle\DependencyInjection\LiipOneallExtension;

class LiipOneallExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\OneallBundle\DependencyInjection\LiipOneallExtension::load
     */
    public function testLoadFailure()
    {
        $container = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $extension = $this->getMockBuilder('Liip\\OneallBundle\\DependencyInjection\\LiipOneallExtension')
            ->getMock();

        $extension->load(array(array()), $container);
    }

    /**
     * @covers Liip\OneallBundle\DependencyInjection\LiipOneallExtension::load
     */
    public function testLoadSetParameters()
    {
        $container = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $parameterBag = $this->getMockBuilder('Symfony\Component\DependencyInjection\ParameterBag\\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $parameterBag
            ->expects($this->any())
            ->method('add');

        $container
            ->expects($this->any())
            ->method('getParameterBag')
            ->will($this->returnValue($parameterBag));

        $extension = new LiipOneallExtension();
        $configs = array(
            array('class' => array('api' => 'foo')),
            array('site_subdomain' => 'foo'),
            array('site_public_key' => 'foo'),
            array('site_private_key' => 'foo'),
            array('social_links' => array('foo')),
        );
        $extension->load($configs, $container);
    }

    /**
     * @covers Liip\OneallBundle\DependencyInjection\LiipOneallExtension::load
     */
    public function testThatCanSetContainerAlias()
    {
        $container = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->once())
            ->method('setAlias')
            ->with($this->equalTo('oneall_alias'), $this->equalTo('liip_oneall.api'));

        $configs = array(
            array('class' => array('api' => 'foo')),
            array('site_subdomain' => 'foo'),
            array('site_public_key' => 'foo'),
            array('site_private_key' => 'foo'),
            array('social_links' => array('foo')),
            array('alias' => 'oneall_alias')
        );
        $extension = new LiipOneallExtension();
        $extension->load($configs, $container);
    }
}
