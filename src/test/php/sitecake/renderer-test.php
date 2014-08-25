<?php
namespace sitecake;

class rendererTest extends \PHPUnit_Framework_TestCaseExt {

	static function setUpBeforeClass() {
		static::mockStaticClass('\\sitecake\\renderer');
		static::mockStaticClass('\\sitecake\\util');
	}
	
	function setUp() {
		renderer::phpunit_cleanup();
		util::phpunit_cleanup();
	}	

	function test_extract_refs() {
		$text = '<body>something 3343.3434 ' .
			'src="path/1234567890123456789012345678901234567890.jpg"> ' .
			'href="someghint0234567890123456789012345678901234567890" ' .
			'src="path/0123456789012345678901234567890123456789.gif" ' .
			'data="//abcdef1234567890123456789012345678901234.doc"';
		$this->assertEquals(array(
			'1234567890123456789012345678901234567890',
			'0123456789012345678901234567890123456789',
			'abcdef1234567890123456789012345678901234'),
			renderer::extract_refs($text));
	}
	
	function test_wrapToScriptTag() {
		$this->assertEquals(1, preg_match(
				'/^<script[^>]*>\s*something\s*<\/script>$/i', 
				renderer::wrapToScriptTag('something')));
	}
	
	function test_normalize_page_id() {
		$html = '<html><head><title>something</title><script>'.
			'var scpageid="1234567890123456789012345678901234567890";'.
			'</script></head>';
		
		renderer::staticExpects($this->never())->method('load_page');
		renderer::staticExpects($this->never())->method('save_page');
		
		renderer::normalize_page_id('path', $html);
		
		renderer::phpunit_verify();		
	}
	
	function test_normalize_page_id2() {
		$html = '<html><head><title>something</title><script>'.
				'var scpageid="1234567890123456789012345678901234567890";'.
				'</script></head>';
	
		renderer::staticExpects($this->any())->
			method('load_page')->
			will($this->returnValue($html));
		
		renderer::staticExpects($this->never())->method('save_page');
	
		renderer::normalize_page_id('path');
	
		renderer::phpunit_verify();
	}

	function test_normalize_page_id3() {
		$html = '<html><head><title>something</title></head><body></body>';
	
		renderer::staticExpects($this->once())->
			method('load_page')->
			will($this->returnValue($html));

		util::staticExpects($this->once())->
			method('id')->
			will($this->returnValue('2893697424798247'));
		
		renderer::staticExpects($this->once())->
			method('save_page')->
			with($this->equalTo('path'), 
				$this->stringContains('"2893697424798247";</script></head>'))->
			will($this->returnValue(true));
			
	
		renderer::normalize_page_id('path');
	
		renderer::phpunit_verify();
	}
	
	function test_normalize_pages() {
		/*
		renderer::staticExpects($this->at(0))->
			method('normalize_page_id')->
			with('path1')->
			will($this->returnValue(true));
		renderer::staticExpects($this->at(1))->
			method('normalize_page_id')->
			with('path2')->
			will($this->returnValue(true));
		*/
		renderer::staticExpects($this->exactly(2))->
			method('normalize_page_id');
		
		renderer::normalize_pages(array('p1' => 'path1', 'p2' => 'path2'));
		renderer::phpunit_verify();
	}
	
}