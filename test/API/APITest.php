<?php
require_once (__DIR__ . '/../../src/User/User.php');
require_once (__DIR__ . '/../../src/API/API.php');

use PHPUnit\Framework\TestCase;
use API\API;
use User\User;

class APITest extends TestCase
{

    public function testProcessApiAllUsers()
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('getAllUsers')->willReturn('');
        $api = new API('users', '', array(), 'GET', $mockUser);
        $this->assertEquals($api->processAPI(), '');
    }

    public function testProcessApiUsers()
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('getUsers')->willReturn('');
        $api = new API('users', 'age 32', array(), 'GET', $mockUser);
        $this->assertEquals($api->processAPI(), '');
    }

    public function testProcessApiAdd()
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('addUser')->willReturn('');
        $api = new API('users', '', array(), 'POST', $mockUser);
        $this->assertEquals($api->processAPI(), '');
    }

    public function testProcessApiInvalidEndpoint()
    {
        $mockUser = $this->createMock(User::class);
        $api = new API('invalid', '', array(), 'GET', $mockUser);
        $this->expectException(Exception::class);
        $api->processAPI();
    }

    public function testProcessApiInvalidMethod()
    {
        $mockUser = $this->createMock(User::class);
        $api = new API('users', '', array(), 'INVALID', $mockUser);
        $this->expectException(Exception::class);
                $api->processAPI();
	}
}
