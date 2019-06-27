<?php
/**
 * Created by PhpStorm.
 * @author Ewgeniy Kiselev <demroos@gmail.com>
 * Date: 26.06.19
 * Time: 18:21
 */

namespace Tests;

use Alvario\OAuth2\AlvarioUser;
use PHPUnit\Framework\TestCase;

class AlvarioUserTest extends TestCase
{
    protected function getRequest()
    {
        return [
            'username' => 'testuser',
            'email' => 'testuser@domain.com',
            'permissions' => [
                'order.list'
            ],
            'scopes' => [
                '1.2'
            ],
            'roles' => [
                'ROLE_USER'
            ],
            'id' => 1,
            'first_name' => 'Test',
            'last_name' => 'User',
        ];
    }

    /** @var AlvarioUser */
    protected $user;

    protected function createUser()
    {
        return new AlvarioUser($this->getRequest());
    }

    public function testGetUsername()
    {
        $this->assertEquals($this->getRequest()['username'], $this->user->getUsername());
    }

    public function test__construct()
    {
        $this->assertEquals($this->getRequest(), $this->user->toArray());
    }

    public function testGetPermissions()
    {
        $this->assertEquals($this->getRequest()['permissions'], $this->user->getPermissions());
    }

    public function testGetId()
    {
        $this->assertEquals($this->getRequest()['id'], $this->user->getId());
    }

    public function testGetEmail()
    {
        $this->assertEquals($this->getRequest()['email'], $this->user->getEmail());
    }

    public function testGetLastName()
    {
        $this->assertEquals($this->getRequest()['last_name'], $this->user->getLastName());
    }

    public function testGetScopes()
    {
        $this->assertEquals($this->getRequest()['scopes'], $this->user->getScopes());
    }

    public function testToArray()
    {
        $this->assertEquals($this->getRequest(), $this->user->toArray());
    }

    public function testGetFirstName()
    {
        $this->assertEquals($this->getRequest()['first_name'], $this->user->getFirstName());
    }

    protected function setUp()
    {
        $this->user = $this->createUser();

        parent::setUp();
    }


}
