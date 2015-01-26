<?php

namespace Sitecake;

class AuthTest extends \PHPUnit_Framework_TestCase {

    public function teardown() {
        \Mockery::close();
    }

	public function test_authenticate() {
		$fs = \Mockery::mock('League\Flysystem\Filesystem');
		$fs->shouldReceive('read')
			->with('/tmp/credentials.php')
			->andReturn('<?php $credentials = "password hash"; ?>')
			->once();

		$auth = new Auth($fs, '/tmp/credentials.php');
		$this->assertFalse($auth->authenticate('wrong password hash'));
		$this->assertFalse($auth->authenticate(null));
		$this->assertTrue($auth->authenticate('password hash'));

	}

	public function test_setCredentials() {
		$fs = \Mockery::mock('League\Flysystem\Filesystem');

		$fs->shouldReceive('read')
			->with('/tmp/credentials.php')
			->andReturn('<?php $credentials = "old hash"; ?>')
			->once();

		$fs->shouldReceive('put')
			->with('/tmp/credentials.php', '/new password hash/')
			->andReturn(true)
			->once();

		$auth = new Auth($fs, '/tmp/credentials.php');
		$this->assertTrue($auth->authenticate('old hash'));
		$auth->setCredentials('new password hash');
		$this->assertTrue($auth->authenticate('new password hash'));
	}	
}