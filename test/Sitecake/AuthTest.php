<?php

namespace Sitecake;

class AuthTest extends \PHPUnit_Framework_TestCase {

	public function testAuthenticate() {
		$GLOBALS['credentials'] = 'password hash';
		$fs = $this->getMockBuilder('League\Flysystem\Filesystem')
			->disableOriginalConstructor()
			->getMock();

		$auth = new Auth($fs, '/tmp/credentials.php');
		$this->assertFalse($auth->authenticate('wrong password hash'));
		$this->assertTrue($auth->authenticate('password hash'));
	}

	public function testSetCredentials() {
		$GLOBALS['credentials'] = 'password hash';
		
		$fs = $this->getMockBuilder('League\Flysystem\Filesystem', array('put'))
			->disableOriginalConstructor()
			->getMock();

		$fs->expects($this->once())->method('put')
			->with('/tmp/credentials.php', $this->stringContains('new password hash'));

		$auth = new Auth($fs, '/tmp/credentials.php');
		$auth->setCredentials('new password hash');
		$this->assertEquals('new password hash', $GLOBALS['credentials']);
	}	
}