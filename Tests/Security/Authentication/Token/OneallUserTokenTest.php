<?php

namespace Liip\OneallBundle\Tests\Security\Authentication\Token;

use Liip\OneallBundle\Security\Authentication\Token\OneallUserToken;

class OneallUserTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testThatAlwaysReturnEmptyCredentials($uid, $roles)
    {
        $token = new OneallUserToken('main', $uid, $roles);

        $this->assertEquals('', $token->getCredentials());
    }

    /**
     * @return array
     */
    public static function provider()
    {
        return array(
            array('', array()),
            array('l3l0', array()),
            array('', array('role1', 'role2')),
            array('l3l0', array('role1', 'role2'))
        );
    }

    public function testThatProviderKeyIsNotEmptyAfterDeserialization()
    {
        $providerKey = 'main';
        $token = unserialize(serialize(new OneallUserToken($providerKey)));

        $this->assertEquals($providerKey, $token->getProviderKey());
    }
}
