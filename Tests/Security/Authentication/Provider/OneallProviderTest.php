<?php

namespace Liip\OneallBundle\Tests\Security\Authentication\Provider;

use Liip\OneallBundle\Security\Authentication\Provider\OneallProvider;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OneallProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThatUserCheckerCannotBeNullWhenUserProviderIsNotNull()
    {
        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->getMock();
        new OneallProvider('main', $oneallMock, $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface'));
    }

    /**
     * @covers Liip\OneallBundle\Security\Authentication\Provider\OneallProvider::authenticate
     */
    public function testThatCannotAuthenticateWhenTokenIsNotOneallUserToken()
    {
        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->getMock();
        $oneallProvider = new OneallProvider('main', $oneallMock);
        $this->assertNull($oneallProvider->authenticate($this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')));
    }

    /**
     * @covers Liip\OneallBundle\Security\Authentication\Provider\OneallProvider::authenticate
     * @covers Liip\OneallBundle\Security\Authentication\Provider\OneallProvider::supports
     */
    public function testThatCannotAuthenticateWhenTokenFromOtherFirewall()
    {
        $providerKeyForProvider = 'main';
        $providerKeyForToken    = 'connect';

        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->getMock();
        $oneallProvider = new OneallProvider($providerKeyForProvider, $oneallMock);

        $tokenMock = $this->getMock('Liip\OneallBundle\Security\Authentication\Token\OneallUserToken', array('getProviderKey'), array($providerKeyForToken));
        $tokenMock->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue($providerKeyForToken));

        $this->assertFalse($oneallProvider->supports($tokenMock));
        $this->assertNull($oneallProvider->authenticate($tokenMock));
    }

    /**
     * @covers Liip\OneallBundle\Security\Authentication\Provider\OneallProvider::authenticate
     * @covers Liip\OneallBundle\Security\Authentication\Provider\OneallProvider::supports
     * @covers Liip\OneallBundle\Security\Authentication\Provider\OneallProvider::createAuthenticatedToken
     */
    public function testThatCanAuthenticateUserWithoutUserProvider()
    {
        $providerKey = 'main';

        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->setMethods(array('getUser'))
            ->getMock();
        $oneallMock->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('123'));

        $oneallProvider = new OneallProvider($providerKey, $oneallMock);

        $tokenMock = $this->getMock('Liip\OneallBundle\Security\Authentication\Token\OneallUserToken', array('getAttributes', 'getProviderKey'), array($providerKey));
        $tokenMock->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue(array()));
        $tokenMock->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue($providerKey));

        $this->assertTrue($oneallProvider->supports($tokenMock));
        $this->assertEquals('123', $oneallProvider->authenticate($tokenMock)->getUser());
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testThatCannotAuthenticateWhenUserProviderThrowsAuthenticationException()
    {
        $providerKey = 'main';

        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->setMethods(array('getUser'))
            ->getMock();
        $oneallMock->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('123'));

        $userProviderMock = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $userProviderMock->expects($this->once())
            ->method('loadUserByUsername')
            ->with('123')
            ->will($this->throwException(new AuthenticationException('test')));

        $userCheckerMock = $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');
        $tokenMock = $this->getMock('Liip\OneallBundle\Security\Authentication\Token\OneallUserToken', array('getProviderKey'), array($providerKey));
        $tokenMock->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue($providerKey));

        $oneallProvider = new OneallProvider($providerKey, $oneallMock, $userProviderMock, $userCheckerMock);
        $oneallProvider->authenticate($tokenMock);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testThatCannotAuthenticateWhenUserProviderDoesNotReturnUsetInterface()
    {
        $providerKey = 'main';

        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->setMethods(array('getUser'))
            ->getMock();
        $oneallMock->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('123'));

        $userProviderMock = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $userProviderMock->expects($this->once())
            ->method('loadUserByUsername')
            ->with('123')
            ->will($this->returnValue('234'));

        $userCheckerMock = $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');
        $tokenMock = $this->getMock('Liip\OneallBundle\Security\Authentication\Token\OneallUserToken', array('getProviderKey'), array($providerKey));
        $tokenMock->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue($providerKey));

        $oneallProvider = new OneallProvider($providerKey, $oneallMock, $userProviderMock, $userCheckerMock);
        $oneallProvider->authenticate($tokenMock);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testThatCannotAuthenticateWhenCannotRetrieveOneallUserFromSession()
    {
        $providerKey = 'main';

        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->setMethods(array('getUser'))
            ->getMock();
        $oneallMock->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue(false));

        $userProviderMock = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $userCheckerMock = $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');

        $tokenMock = $this->getMock('Liip\OneallBundle\Security\Authentication\Token\OneallUserToken', array('getProviderKey'), array($providerKey));
        $tokenMock->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue($providerKey));

        $oneallProvider = new OneallProvider($providerKey, $oneallMock, $userProviderMock, $userCheckerMock);
        $oneallProvider->authenticate($tokenMock);
    }

    /**
     * @covers Liip\OneallBundle\Security\Authentication\Provider\OneallProvider::authenticate
     * @covers Liip\OneallBundle\Security\Authentication\Provider\OneallProvider::createAuthenticatedToken
     */
    public function testThatCanAutenticateUsingUserProvider()
    {
        $providerKey = 'main';

        $userMock = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $userMock->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('l3l0'));
        $userMock->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array()));

        $oneallMock = $this->getMockBuilder('Liip\OneallBundle\Oneall\OneallSessionPersistence')
            ->disableOriginalConstructor()
            ->setMethods(array('getUser'))
            ->getMock();
        $oneallMock->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('123'));

        $userProviderMock = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $userProviderMock->expects($this->once())
            ->method('loadUserByUsername')
            ->with('123')
            ->will($this->returnValue($userMock));

        $userCheckerMock = $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');
        $userCheckerMock->expects($this->once())
            ->method('checkPostAuth');

        $tokenMock = $this->getMock('Liip\OneallBundle\Security\Authentication\Token\OneallUserToken', array('getAttributes', 'getProviderKey'), array($providerKey));
        $tokenMock->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue(array()));
        $tokenMock->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue($providerKey));

        $oneallProvider = new OneallProvider($providerKey, $oneallMock, $userProviderMock, $userCheckerMock);
        $this->assertEquals('l3l0', $oneallProvider->authenticate($tokenMock)->getUsername());
    }
}
