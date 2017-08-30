<?php

use PHPUnit\Framework\TestCase;
use User\User;

class UserTest extends TestCase
{
	public function testGetUsersNoUser()
	{
		$mockPdoStatement = $this->createMock(PDOStatement::class);
                $mockPdoStatement->method('rowCount')->willReturn(0);

		$mockPdo = $this->createMock(PDO::class);
                $mockPdo->method('query')->willReturn($mockPdoStatement);

                $mockDbHelper = $this->createMock(DbHelper::class);
                $mockDbHelper->method('getConnection')->willReturn($mockPdo);

                $user = new User($mockDbHelper);
                $str = @$user->getUsers('some search string');
		$this->assertEquals($str, '[]');
	}

	public function testGetUsersOneUser()
	{
		$mockPdoStatement = $this->createMock(PDOStatement::class);
		$mockPdoStatement->method('rowCount')->willReturn(1);
		$mockPdoStatement->method('fetch')->willReturn(array(
			'email' => 'test@test.com',
			'phone_number' => '1234567890',
			'full_name' => 'John Doe',
			'key' => 'abcdef1234567890',
			'account_key' => '1234567890abcdef',
			'metadata' => 'some metadata'
		));

		$mockPdo = $this->createMock(PDO::class);
		$mockPdo->method('query')->willReturn($mockPdoStatement);

		$mockDbHelper = $this->createMock(DbHelper::class);
		$mockDbHelper->method('getConnection')->willReturn($mockPdo);

		$user = new User($mockDbHelper);
		$str = $user->getUsers('some search string');
		$this->assertEquals($str, '{"email":"test@test.com","phone_number":"1234567890","full_name":"John Doe","key":"abcdef1234567890","account_key":"1234567890abcdef","metadata":"some metadata"}');
	}

	public function testGetUsersMultipleUsers()
        {
                $mockPdoStatement = $this->createMock(PDOStatement::class);
                $mockPdoStatement->method('rowCount')->willReturn(2);

                $mockPdo = $this->createMock(PDO::class);
                $mockPdo->method('query')->willReturn($mockPdoStatement);

                $mockDbHelper = $this->createMock(DbHelper::class);
                $mockDbHelper->method('getConnection')->willReturn($mockPdo);

                $user = new User($mockDbHelper);
                $str = $user->getUsers('some search string');
                $this->assertEquals($str, '{"users":[]}');
        }

	public function testGetAllUsersNoUser()
        {
                $mockPdoStatement = $this->createMock(PDOStatement::class);
                $mockPdoStatement->method('rowCount')->willReturn(0);

                $mockPdo = $this->createMock(PDO::class);
                $mockPdo->method('query')->willReturn($mockPdoStatement);

                $mockDbHelper = $this->createMock(DbHelper::class);
                $mockDbHelper->method('getConnection')->willReturn($mockPdo);

                $user = new User($mockDbHelper);
                $str = @$user->getAllUsers();
                $this->assertEquals($str, '[]');
        }

	public function testGetAllUsersOneUser()
        {
                $mockPdoStatement = $this->createMock(PDOStatement::class);
                $mockPdoStatement->method('rowCount')->willReturn(1);
                $mockPdoStatement->method('fetch')->willReturn(array(
                        'email' => 'test@test.com',
                        'phone_number' => '1234567890',
                        'full_name' => 'John Doe',
                        'key' => 'abcdef1234567890',
                        'account_key' => '1234567890abcdef',
                        'metadata' => 'some metadata'
                ));

                $mockPdo = $this->createMock(PDO::class);
                $mockPdo->method('query')->willReturn($mockPdoStatement);

                $mockDbHelper = $this->createMock(DbHelper::class);
                $mockDbHelper->method('getConnection')->willReturn($mockPdo);

                $user = new User($mockDbHelper);
                $str = $user->getAllUsers();
                $this->assertEquals($str, '{"email":"test@test.com","phone_number":"1234567890","full_name":"John Doe","key":"abcdef1234567890","account_key":"1234567890abcdef","metadata":"some metadata"}');
        }

	public function testGetAllUsersMultipleUsers()
        {
                $mockPdoStatement = $this->createMock(PDOStatement::class);
                $mockPdoStatement->method('rowCount')->willReturn(2);

                $mockPdo = $this->createMock(PDO::class);
                $mockPdo->method('query')->willReturn($mockPdoStatement);

                $mockDbHelper = $this->createMock(DbHelper::class);
                $mockDbHelper->method('getConnection')->willReturn($mockPdo);

                $user = new User($mockDbHelper);
                $str = $user->getAllUsers();
                $this->assertEquals($str, '{"users":[]}');
        }

	public function testAddUser()
	{
                $mockPdo = $this->createMock(PDO::class);
                $mockPdo->method('exec');
		$mockPdo->method('errorInfo')->willReturn(array(0, NULL, NULL));

		$mockDbHelper = $this->createMock(DbHelper::class);
                $mockDbHelper->method('getConnection')->willReturn($mockPdo);

		$user = new User($mockDbHelper);
		$str = @$user->addUser(
			array(
				'email' => 'test@test.com',
				'phone_number' => '1234567890',
				'full_name' => 'John Doe',
				'password' => 'abc',
				'metadata' => 'some metadata'
			),
			1,
			1
		);
		$this->assertEquals($str, '{"email":"test@test.com","phone_number":"1234567890","full_name":"John Doe","password":"baac6e1078f81904e0c88168063907d0efc8e3c1cfc5b44f37dd8a29956dbee6","key":"5d07ba1279e52dcd260c712633fd12a8bc332e6352d1ccafe8fdc9dde2be0dc6","account_key":null,"metadata":"some metadata"}');
	}

	public function testAddUserDuplicateEmail()
        {
                $mockPdo = $this->createMock(PDO::class);
                $mockPdo->method('exec');
                $mockPdo->method('errorInfo')->willReturn(array(1, 100, 'Duplicate key'));

                $mockDbHelper = $this->createMock(DbHelper::class);
                $mockDbHelper->method('getConnection')->willReturn($mockPdo);

                $user = new User($mockDbHelper);
                $str = @$user->addUser(
                        array(
                                'email' => 'test@test.com',
                                'phone_number' => '1234567890',
                                'full_name' => 'John Doe',
                                'password' => 'abc',
                                'metadata' => 'some metadata'
                        ),
                        1,
                        1
                );
                $this->assertEquals($str, '{"errors":[1,100,"Duplicate key"]}');
        }

	public function testAddUserEmptyEmailAndPhoneNumber()
        {
                $mockPdo = $this->createMock(PDO::class);
                $mockDbHelper = $this->createMock(DbHelper::class);
                $mockDbHelper->method('getConnection')->willReturn($mockPdo);

                $user = new User($mockDbHelper);
                $str = @$user->addUser(
                        array(
                                'email' => '',
                                'phone_number' => '',
                                'full_name' => 'John Doe',
                                'password' => 'abc',
                                'metadata' => 'some metadata'
                        ),
                        1,
                        1
                );
                $this->assertEquals($str, '{"errors":["Email is empty","Invalid email format","Phone number is empty","Invalid phone number format"]}');
        }
}
