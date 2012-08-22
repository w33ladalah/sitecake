<?php
namespace sitecake;

class metaTest extends \PHPUnit_Framework_TestCaseExt {

	static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\meta');
	}

	function test_path() {
		$GLOBALS['DRAFT_CONTENT_DIR'] = 'draft-dir';
		$this->assertEquals('draft-dir' . DS . 'meta.data', meta::path());
	}
	
	function test_exists() {
		meta::$data = array(
			'1234' => array('something')
		);
		
		$this->assertTrue(meta::exists('1234'));
		$this->assertFalse(meta::exists('1111'));
	}
	
	function test_get() {
		meta::$data = array(
			'12' => array('prop1' => 'val1')
		);		

		$this->assertEquals('val1', meta::get('12', 'prop1'));
		$this->assertEquals(array('prop1' => 'val1'), meta::get('12'));	
	}
	
	function test_put() {
		meta::$data = array();
		meta::put('123', array('prop1' => 'val1'));
		$this->assertEquals(array('prop1' => 'val1'), meta::$data['123']);
	}
	
}