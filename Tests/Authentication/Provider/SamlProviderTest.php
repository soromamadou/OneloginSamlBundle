<?php

namespace Hslavich\OneloginSamlBundle\Tests\Authentication\Provider;

use Hslavich\OneloginSamlBundle\Security\Authentication\Provider\SamlProvider;
use Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlTokenFactory;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class SamlProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testAuthenticate()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $provider = $this->getProvider($user);
        $token = $provider->authenticate($this->getSamlToken());

        $this->assertInstanceOf('Hslavich\\OneloginSamlBundle\\Security\\Authentication\\Token\\SamlToken', $token);
        $this->assertEquals(array('foo' => 'bar'), $token->getAttributes());
        $this->assertEquals(array(), $token->getRoles());
        $this->assertTrue($token->isAuthenticated());
        $this->assertSame($user, $token->getUser());
    }

    public function testAuthenticateWithUserFactory()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $userFactory = $this->getMock('Hslavich\OneloginSamlBundle\Security\User\SamlUserFactoryInterface');
        $userFactory->expects($this->once())->method('createUser')->will($this->returnValue($user));

        $provider = $this->getProvider(null, $userFactory);
        $token = $provider->authenticate($this->getSamlToken());

        $this->assertInstanceOf('Hslavich\\OneloginSamlBundle\\Security\\Authentication\\Token\\SamlToken', $token);
        $this->assertEquals(array('foo' => 'bar'), $token->getAttributes());
        $this->assertEquals(array(), $token->getRoles());
        $this->assertTrue($token->isAuthenticated());
        $this->assertSame($user, $token->getUser());
    }

    public function testSamlAttributesInjection()
    {
        $user = $this->getMock('Hslavich\OneloginSamlBundle\Security\User\SamlUserInterface');
        $user->expects($this->once())->method('getRoles')->will($this->returnValue(array()));
        $user->expects($this->once())->method('setSamlAttributes')->with($this->equalTo(array('foo' => 'bar')));

        $provider = $this->getProvider($user);
        $provider->authenticate($this->getSamlToken());
    }

    protected function getSamlToken()
    {
        $token = $this->getMock('Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlToken', array('getUsername'), array(), '', false);
        $token->expects($this->once())->method('getUsername')->will($this->returnValue('admin'));
        $token->setAttributes(array('foo' => 'bar'));

        return $token;
    }

    protected function getProvider($user = null, $userFactory = null)
    {
        $userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        if ($user) {
            $userProvider->expects($this->once())->method('loadUserByUsername')->will($this->returnValue($user));
        } else {
            $userProvider->expects($this->once())->method('loadUserByUsername')->will($this->throwException(new UsernameNotFoundException()));
        }

        $provider = new SamlProvider($userProvider);
        $provider->setTokenFactory(new SamlTokenFactory());

        if ($userFactory) {
            $provider->setUserFactory($userFactory);
        }

        return $provider;
    }
}
