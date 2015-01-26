<?php

namespace Sitecake\Filesystem;

class CopyPathsTest extends \PHPUnit_Framework_TestCase {
	
    public function teardown() {
        \Mockery::close();
    }

	public function test_handle() {
		$fs = \Mockery::mock('League\Flysystem\Filesystem');

		$fs->shouldReceive('copy')
			->with('p1', 'dpath/p1')
			->andReturn(true)
			->once();

		$fs->shouldReceive('copy')
			->with('p2', 'dpath/p2')
			->andReturn(true)
			->once();

		$fs->shouldReceive('copy')
			->with('s1/p3', 'dpath/p3')
			->andReturn(true)
			->once();

		$plugin = new CopyPaths();
		$plugin->setFilesystem($fs);

		$plugin->handle(array('p1', 'p2'), '', 'dpath');
		$plugin->handle(array('s1/p3'), 's1/', 'dpath');
	}
}