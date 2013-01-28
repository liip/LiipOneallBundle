<?php

namespace Liip\OneallBundle\Tests\Security\EntryPoint;

use Liip\OneallBundle\Security\EntryPoint\OneallAuthenticationEntryPoint;

class OneallAuthenticationEntryPointTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\OneallBundle\Security\EntryPoint\OneallAuthenticationEntryPoint::start
     */
    public function testThatRedirectResponseWithOneallLoginUrlIsCreated()
    {
        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request', array('getUriForPath'));
        $requestMock->expects($this->once())
            ->method('getUriForPath')
            ->with($this->equalTo('/index'))
            ->will($this->returnValue('http://localhost/index'));

        $options = array('check_path' => '/index');
        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallApi')
            ->disableOriginalConstructor()
            ->setMethods(array('getLoginUrl'))
            ->getMock();
        $oneallMock->expects($this->once())
            ->method('getLoginUrl')
            ->with($this->equalTo(array(
                'display' => 'page',
                'scope' => 'email,user_website',
                'redirect_uri' => 'http://localhost/index'
            )))
            ->will($this->returnValue('http://localhost/oneall-redirect/index'));

        $oneallAuthentication = new OneallAuthenticationEntryPoint($oneallMock, $options, array('email', 'user_website'));
        $response = $oneallAuthentication->start($requestMock);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response, 'RedirectResponse is returned');
        $this->assertEquals($response->headers->get('location'), 'http://localhost/oneall-redirect/index', 'RedirectResponse has defined expected location');
    }

    /**
     * @covers Liip\OneallBundle\Security\EntryPoint\OneallAuthenticationEntryPoint::start
     */
    public function testThatRedirectionToOneallLoginUrlIsCreated()
    {
        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request', array('getUriForPath'));

        $options = array('check_path' => '/index', 'server_url' => 'http://server.url', 'app_url' => 'http://app.url');
        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallApi')
            ->disableOriginalConstructor()
            ->setMethods(array('getLoginUrl'))
            ->getMock();
        $oneallMock->expects($this->once())
            ->method('getLoginUrl')
            ->will($this->returnValue('http://localhost/oneall-redirect/index'));

        $oneallAuthentication = new OneallAuthenticationEntryPoint($oneallMock, $options, array());
        $response = $oneallAuthentication->start($requestMock);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response, 'Response is returned');
        $this->assertRegExp('/location\.href="http:\/\/localhost\/oneall-redirect\/index/', $response->getContent(), 'Javascript redirection is in response');
    }
}
