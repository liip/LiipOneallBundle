<?php

namespace Liip\OneallBundle\Tests\Twig\Extension;

use Liip\OneallBundle\Twig\Extension\OneallExtension;

class OneallExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\OneallBundle\Twig\Extension\OneallExtension::getName
     */
    public function testGetName()
    {
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $extension = new OneallExtension($containerMock);
        $this->assertSame('oneall', $extension->getName());
    }

    /**
     * @covers Liip\OneallBundle\Twig\Extension\OneallExtension::getFunctions
     */
    public function testGetFunctions()
    {
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $extension = new OneallExtension($containerMock);
        $functions = $extension->getFunctions();
        $this->assertInstanceOf('\Twig_Function_Method', $functions['oneall_initialize']);
        $this->assertInstanceOf('\Twig_Function_Method', $functions['oneall_login_button']);
    }

    /**
     * @covers Liip\OneallBundle\Twig\Extension\OneallExtension::renderInitialize
     */
    public function testRenderInitialize()
    {
        $helperMock = $this->getMockBuilder('Liip\OneallBundle\Twig\Extension\OneallExtension')
            ->disableOriginalConstructor()
            ->getMock();
        $helperMock->expects($this->once())
            ->method('initialize')
            ->will($this->returnValue('returnedValue'));
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $containerMock->expects($this->once())
            ->method('get')
            ->with('liip_oneall.helper')
            ->will($this->returnValue($helperMock));
 
        $extension = new OneallExtension($containerMock);
        $this->assertSame('returnedValue', $extension->renderInitialize());
    }
    
    /**
     * @covers Liip\OneallBundle\Twig\Extension\OneallExtension::renderloginButton
     */
    public function testRenderLoginButton()
    {
        $helperMock = $this->getMockBuilder('Liip\OneallBundle\Twig\Extension\OneallExtension')
            ->disableOriginalConstructor()
            ->getMock();
        $helperMock->expects($this->once())
            ->method('loginButton')
            ->will($this->returnValue('returnedValueLogin'));
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $containerMock->expects($this->once())
            ->method('get')
            ->with('liip_oneall.helper')
            ->will($this->returnValue($helperMock));
 
        $extension = new OneallExtension($containerMock);
        $this->assertSame('returnedValueLogin', $extension->renderLoginButton());
    }

    /**
     * @covers Liip\OneallBundle\Twig\Extension\OneallExtension::initialize
     */
    public function testInitialize()
    {
        $expected = new \stdClass();

        $templating = $this->getMockBuilder('Symfony\Component\Templating\DelegatingEngine')
            ->disableOriginalConstructor()
            ->getMock();
        $templating
            ->expects($this->once())
            ->method('render')
            ->with('LiipOneallBundle::initialize.html.php', array(
                'appId'   => 123,
                'async'   => true,
                'cookie'  => false,
                'culture' => 'en_US',
                'fbAsyncInit' => '',
                'logging' => true,
                'oauth' => true,
                'status'  => false,
                'xfbml'   => false,
            ))
            ->will($this->returnValue($expected));

        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->setMethods(array('getAppId'))
            ->getMock();
        $oneallMock->expects($this->once())
            ->method('getAppId')
            ->will($this->returnValue('123'));

        $helper = new OneallExtension($templating, $oneallMock);
        $this->assertSame($expected, $helper->initialize(array('cookie' => false)));
    }

    /**
     * @covers Liip\OneallBundle\Twig\Extension\OneallExtension::loginButton
     */
    public function testLoginButton()
    {
        $expected = new \stdClass();

        $templating = $this->getMockBuilder('Symfony\Component\Templating\DelegatingEngine')
            ->disableOriginalConstructor()
            ->getMock();
        $templating
            ->expects($this->once())
            ->method('render')
            ->with('LiipOneallBundle::loginButton.html.php', array(
                'autologoutlink' => 'false',
                'label'          => 'testLabel',
                'scope'          => '1,2,3',
            ))
            ->will($this->returnValue($expected));

        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->setMethods(array('getAppId'))
            ->getMock();
        $oneallMock->expects($this->any())
            ->method('getAppId');

        $helper = new OneallExtension($templating, $oneallMock, true, 'en_US', array(1,2,3) );
        $this->assertSame($expected, $helper->loginButton(array('label' => 'testLabel')));
    }
}
