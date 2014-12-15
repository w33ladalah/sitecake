<?php

namespace Sitecake\Filesystem;

class ListPatternPathsTest extends \PHPUnit_Framework_TestCase {
	
    public function teardown() {
        \Mockery::close();
    }
    	
	public function test_handle() {
		$fs = \Mockery::mock('League\Flysystem\Filesystem');

		$fs->shouldReceive('listPaths')
			->with('', false)
			->andReturn(array('path1', 'path2'));

		$fs->shouldReceive('listPaths')
			->with('a', false)
			->andReturn(array('a/path1', 'a/path2'));

		$fs->shouldReceive('listPaths')
			->with('p', false)
			->andReturn(array('p/p1', 'p/p2'));

		$plugin = new ListPatternPaths();
		$plugin->setFilesystem($fs);

		$res = $plugin->handle('', '/nothing/');
		$this->assertTrue(is_array($res));
		$this->assertEquals(0, count($res));

		$res = $plugin->handle('a', '/.*/');		
		$this->assertTrue(is_array($res));
		$this->assertEquals(2, count($res));
		$this->assertEquals('a/path1', $res[0]);
		$this->assertEquals('a/path2', $res[1]);

		$res = $plugin->handle('p', '/p\/p1/');		
		$this->assertTrue(is_array($res));
		$this->assertEquals(1, count($res));
		$this->assertEquals('p/p1', $res[0]);            
	}
}