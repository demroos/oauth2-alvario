<?php
/**
 * Created by PhpStorm.
 * @author Ewgeniy Kiselev <demroos@gmail.com>
 * Date: 26.06.19
 * Time: 18:01
 */

namespace Tests;

use Alvario\OAuth2\AlvarioAuthProvider;
use Alvario\OAuth2\AlvarioUser;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AlvarioAuthProviderTest extends TestCase
{
    /** @var AlvarioAuthProvider */
    protected $provider;

    public function testChangeHostUrl()
    {
        $provider = new AlvarioAuthProvider(['hostUrl' => 'http://example.com']);

        $this->assertEquals(
            'http://example.com/oauth/v2/auth',
            $provider->getBaseAuthorizationUrl()
        );
    }


    public function testGetBaseAuthorizationUrl()
    {
        $this->assertEquals(
            'https://user-management.alvar.io/oauth/v2/auth',
            $this->provider->getBaseAuthorizationUrl()
        );
    }

    public function testGetBaseAccessTokenUrl()
    {
        $this->assertEquals(
            'https://user-management.alvar.io/oauth/v2/token',
            $this->provider->getBaseAccessTokenUrl([])
        );
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        $this->assertEquals(
            'https://user-management.alvar.io/api/profile/info',
            $this->provider->getResourceOwnerDetailsUrl(new AccessToken(['access_token' => 'token']))
        );
    }

    public function testGetDefaultScopes()
    {
        $this->assertEquals([], $this->invokeMethod($this->provider, 'getDefaultScopes'));
    }

    protected function createProvider()
    {
        return new AlvarioAuthProvider();
    }

    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testCheckResponseEmpty()
    {
        $response = $this->createMock(Response::class);
        $data = [];
        $this->assertEquals(null, $this->invokeMethod($this->provider, 'checkResponse', [$response, $data]));
    }

    public function testCheckResponseWithError()
    {
        $response = $this->createMock(Response::class);
        $data = [
            'error' => 'test error'
        ];
        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('test error');
        $this->invokeMethod($this->provider, 'checkResponse', [$response, $data]);
    }

    public function testCheckResponseWithErrorArray()
    {
        $response = $this->createMock(Response::class);
        $data = [
            'error' => [
                'message' => 'test error message',
                'code' => 102,
            ]
        ];
        $this->expectException(IdentityProviderException::class);
        $this->expectExceptionCode(102);
        $this->expectExceptionMessage('test error message');
        $this->invokeMethod($this->provider, 'checkResponse', [$response, $data]);
    }

    protected function setUp()
    {
        $this->provider = $this->createProvider();

        parent::setUp();
    }

    public function testCreateResourceOwner()
    {
        $token = $this->createMock(AccessToken::class);
        $response = [];
        $user = $this->invokeMethod($this->provider, 'createResourceOwner', [$response, $token]);
        $this->assertTrue($user instanceof AlvarioUser);
    }

    public function testGetAuthorizationHeaders()
    {
        $headers = $this->invokeMethod($this->provider, 'getAuthorizationHeaders', ['test_token']);
        $expectedHeaders = [
            'Authorization' => 'Bearer test_token'
        ];
        $this->assertEquals($expectedHeaders, $headers);
    }


}
