<?php
namespace sitecake;

class imageTest extends \PHPUnit_Framework_TestCaseExt {

	static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\image');
	}

	function test_combine_transform() {
		$this->assertEquals('100:50:0:0:100:50', 
			image::combine_transform('100:50:0:0:50:25', '50:25:0:0:100:50'));
		$this->assertEquals('100:50:50:25:100:50', 
			image::combine_transform('100:50:50:25:50:25', '50:25:0:0:100:50'));
	}
	
	function test_image_info() {
		$GLOBALS['SC_ROOT'] = 'sc-test';
		$this->assertEquals(array(
				'id' => '1234',
				'ext' => 'jpg',
				'path' => 'sc-test' . DS . 'sitecake-content/1234.jpg',
				'name' => '1234.jpg'
			), 
			image::image_info('sitecake-content/1234.jpg'));
		$this->assertEquals(array(
				'id' => '123',
				'ext' => 'jpg',
				'path' => 'sc-test' . DS . 'images/test/ui/123.4.jpg',
				'name' => '123.4.jpg'
			), 
			image::image_info('images/test/ui/123.4.jpg'));
	}	
}