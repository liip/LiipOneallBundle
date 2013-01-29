<?php

namespace Liip\OneallBundle\Tests\Twig\Extension;

use Liip\OneallBundle\Twig\Extension\OneallExtension;
use Symfony\Component\HttpFoundation\Request;

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
        $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $templating->expects($this->once())
            ->method('render')
            ->will($this->returnValue('returnedValue'));
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $containerMock->expects($this->once())
            ->method('get')
            ->with('templating')
            ->will($this->returnValue($templating));
 
        $extension = new OneallExtension($containerMock);
        $this->assertSame('returnedValue', $extension->renderInitialize());
    }
    
    /**
     * @covers Liip\OneallBundle\Twig\Extension\OneallExtension::renderloginButton
     */
    public function testRenderLoginButton()
    {
        $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $templating->expects($this->once())
            ->method('render')
            ->will($this->returnValue('returnedValue'));

        $router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $containerMock->expects($this->any())
            ->method('get')
            ->will($this->onConsecutiveCalls($templating, $router));
        $containerMock->expects($this->any())
            ->method('getParameter')
            ->will($this->onConsecutiveCalls('/foo', null, '', array('check_path' => array('/foo'))));

        $extension = new OneallExtension($containerMock);
        $this->assertSame('returnedValue', $extension->renderLoginButton());
    }
}
