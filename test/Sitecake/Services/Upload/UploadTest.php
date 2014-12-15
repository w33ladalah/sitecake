<?php

namespace Sitecake\Services\Upload;

function fopen($path, $mode) {
	return 1;
}

function uniqid() {
	return '1234567890123';
}

class UploadTest extends \PHPUnit_Framework_TestCase {
	
    public function teardown() {
        \Mockery::close();
    }
    	
	public function test_save() {
		$fs = \Mockery::mock('League\Flysystem\Filesystem');

		$fs->shouldReceive('writeStream')
			->with('draft/path/images/name-sc1234567890123.jpg', 1)
			->andReturn(true);

		$fs->shouldReceive('writeStream')
			->with('draft/path/files/name2-sc1234567890123.doc', 1)
			->andReturn(true);

		$upload = new Upload($fs, 'draft/path');

		$res = $upload->save('name.jpg');
		$this->assertEquals('draft/path/images/name-sc1234567890123.jpg', $res);

		$res = $upload->save('name2.doc');
		$this->assertEquals('draft/path/files/name2-sc1234567890123.doc', $res);
	}
}