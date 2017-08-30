<?php

require_once(__DIR__ . '../../src/DbHelper.php');

use PHPUnit\Framework\TestCase;

class DbHelperTest extends TestCase
{
	public function testGetConnection()
	{
		$this->assertTrue(true);
		$mockPdo = $this->createMock(DbHelper::class);
		$dbHelper = new DbHelper($mockPdo);
		$this->assertEquals($mockPdo, $dbHelper->getConnection());
	}
}
