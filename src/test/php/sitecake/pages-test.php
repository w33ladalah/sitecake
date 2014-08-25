<?php
namespace sitecake;

use \phpQuery as phpQuery;

class pagesTest extends \PHPUnit_Framework_TestCaseExt {

	static function setUpBeforeClass() {		
		static::mockStaticClass('\\sitecake\\io');
		static::mockStaticClass('\\sitecake\\pages');
	}

	function setUp() {
		io::phpunit_cleanup();
	}
	
	function test_page_files() {
		@define('SC_ROOT', '/some/path');
		io::staticExpects($this->any())
			->method('glob')
			->will($this->returnValue(
				array('/some/path/file1.html', '/some/path/file2.html')));
		$this->assertEquals(array('file1.html' => '/some/path/file1.html', 
			'file2.html' => '/some/path/file2.html'), pages::page_files());
	}

	function test_page_files2() {
		@define('SC_ROOT', '/some/dir');
		io::staticExpects($this->any())
			->method('glob')
			->will($this->returnValue(array()));
		$this->assertEquals(array(), pages::page_files());
	}

	function test_page_files3() {
		@define('SC_ROOT', '/some/dir');
		io::staticExpects($this->any())
			->method('glob')
			->will($this->returnValue(null));
		$this->assertEquals(array(), pages::page_files());
	}
	
	function test_rpath2url() {
		$this->assertEquals('index.html', 
			pages::rpath2url('index.html'));
		$this->assertEquals('sub/index.html', 
			pages::rpath2url('sub/index.html'));
		$this->assertEquals('sub/index.html',
			pages::rpath2url('sub\index.html'));		
	}
	
	function test_url2rpath() {
		$this->assertEquals('index.html',
			pages::url2rpath('index.html'));
		$this->assertEquals('sub/index.html',
			pages::url2rpath('sub/index.html'));						
	}
	
	function test_page_id() {
		$this->assertEquals('1234', 
			pages::page_id('<script>var scpageid = "1234";</script>'));
		$this->assertFalse(
			pages::page_id('<script>var scpage = "1234";</script>'));
		
	}
	
	function test_nav_urls() {
		$html = '<html><body><ul class="sc-nav">'.
			'<li><a href="page1.html">Page1</a></li>'.
			'<li><a href="page2.html">Page2</a></li></ul></body></html>';
		$doc = phpQuery::newDocument($html);
		$this->assertEquals(
			array('page1.html' => 'Page1', 'page2.html' => 'Page2'), 
			pages::nav_urls($doc));
	}
	
	function test_page_url_slug() {
		$this->assertEquals('my-exotic-url.html', 
			pages::page_url_slug('my // exotic. URL.html'));
	}
	
}