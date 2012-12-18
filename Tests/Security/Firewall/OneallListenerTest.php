<?php

namespace Liip\OneallBundle\Tests\Security\Firewall\OneallListener;

use Liip\OneallBundle\Security\Firewall\OneallListener;

class OneallListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Liip\OneallBundle\Security\Firewall\OneallListener::attemptAuthentication
     */
    public function testThatCanAttemptAuthenticationWithOneall()
    {
        $listener = new OneallListener(
            $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface'),
            $this->getAuthenticationManager(),
            $this->getMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface'),
            $this->getHttpUtils(),
            'providerKey',
            $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface'),
            $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface')
        );
        $listener->handle($this->getResponseEvent());
    }

    /**
     * @return Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    private function getAuthenticationManager()
    {
        $authenticationManagerMock = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $authenticationManagerMock->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Liip\OneallBundle\Security\Authentication\Token\OneallUserToken'));

        return $authenticationManagerMock;
    }

    /**
     * @return Symfony\Component\Security\Http\HttpUtils
     */
    private function getHttpUtils()
    {
        $httpUtils = $this->getMock('Symfony\Component\Security\Http\HttpUtils');
        $httpUtils->expects($this->once())
            ->method('checkRequestPath')
            ->will($this->returnValue(true));

        return $httpUtils;
    }

    /**
     * @return Symfony\Component\HttpKernel\Event\GetResponseEvent
     */
    private function getResponseEvent()
    {
        $responseEventMock = $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', array('getRequest'), array(), '', false);
        $responseEventMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->getRequest()));

        return $responseEventMock;
    }

    /**
     * @return Symfony\Component\HttpFoundation\Request
     */
    private function getRequest()
    {
        $requestMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalClone()
            ->getMock();
        $requestMock->expects($this->any())
            ->method('hasSession')
            ->will($this->returnValue('true'));
        $requestMock->expects($this->any())
            ->method('hasPreviousSession')
            ->will($this->returnValue('true'));

        return $requestMock;
    }
}
