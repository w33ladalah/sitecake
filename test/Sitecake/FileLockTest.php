<?php

namespace Sitecake;

class FileLockTest extends \PHPUnit_Framework_TestCase {

	public function testSet() {
		$fs = $this->getMockBuilder('League\Flysystem\Filesystem', array('put'))
			->disableOriginalConstructor()
			->getMock();

		$fs->expects($this->exactly(3))->method('put')
			->withConsecutive(
				array($this->equalTo('/tmp/test1.lock'), $this->equalTo('0')),
				array($this->equalTo('/tmp/test2.lock'), $this->equalTo('0')),
				array($this->equalTo('/tmp/test3.lock'), $this->greaterThan(microtime())));

		$fl = new FileLock($fs, '/tmp');
		$fl->set('test1');
		$fl->set('test2', 0);
		$fl->set('test3', 10000);
	}

	public function testExists() {
		$fs = $this->getMockBuilder('League\Flysystem\Filesystem', array('read', 'has', 'delete'))
			->disableOriginalConstructor()
			->getMock();

		$fs->expects($this->once())->method('delete')
			->with('/tmp/test3.lock')
			->will($this->returnValue(true));

		$fs->method('has')
             ->will($this->returnValueMap(array(
             		array('/tmp/test1.locl', false),
             		array('/tmp/test2.lock', true),
             		array('/tmp/test3.lock', true)
             	)));

		$fs->method('read')
             ->will($this->returnValueMap(array(
             		array('/tmp/test2.lock', (string)(round(microtime(true) * 1000) + 10000)),
             		array('/tmp/test3.lock', '10000')
             	)));

		$fl = new FileLock($fs, '/tmp');

		$this->assertFalse($fl->exists('test1'));
		$this->assertTrue($fl->exists('test2'));
		$this->assertFalse($fl->exists('test3'));
	}	
}