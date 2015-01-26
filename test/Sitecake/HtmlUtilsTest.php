<?php

namespace Sitecake;

use \phpQuery;

class HtmlUtilsTest extends \PHPUnit_Framework_TestCase {

	function test_strToDoc() {
		$html = '<html><head><meta charset="UTF8"></head><body></body></html>';
		$doc = HtmlUtils::strToDoc($html);

		$this->assertTrue(is_object($doc));
		$this->assertTrue($doc->isHtml());
	}

	function test_toDoc() {
		$html = '<html><head><meta charset="UTF8"></head><body></body></html>';
		$doc = HtmlUtils::toDoc($html);

		$this->assertTrue(is_object($doc));
		$this->assertTrue($doc->isHtml());

		$doc1 = HtmlUtils::toDoc($doc);

		$this->assertTrue(is_object($doc1));
		$this->assertTrue($doc1->isHtml());
		$this->assertEquals($doc1, $doc);		
	}


	function test_appendToHead() {
		$html = '<html><head><meta charset="UTF8"></head><body></body></html>';
		$this->assertFalse(is_object($html));
		$doc = HtmlUtils::appendToHead($html, '<script id="s1">var s;</script>');

		$this->assertTrue(is_object($doc));
		$this->assertTrue(isset(phpQuery::pq('#s1')->elements[0]));
	}


	function test_isAbsoluteURL() {
		$this->assertTrue(HtmlUtils::isAbsoluteURL('https://google.com/'));
		$this->assertTrue(HtmlUtils::isAbsoluteURL('http://google.com/something?test&z=23#pest=2'));
		$this->assertFalse(HtmlUtils::isAbsoluteURL('test/t.html'));
	}

	function test_isRelativeURL() {
		$this->assertTrue(HtmlUtils::isRelativeURL('test/relative.html'));
		$this->assertFalse(HtmlUtils::isRelativeURL('http://com.exe/test/relative.html'));
	}

	function test_prefixNodeAttr() {
		$doc = phpQuery::newDocument('<img src="img0.jpg" srcset="img1.jpg 1000w, img2.jpg" href="link.html"/>');
		$img = phpQuery::pq('img', $doc)->elements[0];
		$img = HtmlUtils::prefixNodeAttr($img, 'src', 'p1/');
		$img = HtmlUtils::prefixNodeAttr($img, 'srcset', 'p2/');
		$img = HtmlUtils::prefixNodeAttr($img, 'href', 'p3/');

		$this->assertEquals('p1/img0.jpg', $img->getAttribute('src'));
		$this->assertEquals('p2/img1.jpg 1000w, p2/img2.jpg', $img->getAttribute('srcset'));
		$this->assertEquals('p3/link.html', $img->getAttribute('href'));

		$doc = phpQuery::newDocument('<img src="img0.jpg" srcset="img1.jpg 1000w, img2.jpg" href="link.html"/>');
		$img = phpQuery::pq('img', $doc)->elements[0];
		$test = function($val) {
			return ($val === 'img0.jpg' || $val === 'img2.jpg');
		};
		$img = HtmlUtils::prefixNodeAttr($img, 'src', 'p1/', $test);
		$img = HtmlUtils::prefixNodeAttr($img, 'srcset', 'p2/', $test);
		$img = HtmlUtils::prefixNodeAttr($img, 'href', 'p3/', $test);

		$this->assertEquals('p1/img0.jpg', $img->getAttribute('src'));
		$this->assertEquals('img1.jpg 1000w, p2/img2.jpg', $img->getAttribute('srcset'));
		$this->assertEquals('link.html', $img->getAttribute('href'));				
	}

	function test_prefixNodeAttrs() {
		$doc = phpQuery::newDocument('<img src="img0.jpg" srcset="img1.jpg 1000w, img2.jpg" href="link.html"/>');
		$img = phpQuery::pq('img', $doc)->elements[0];
		$img = HtmlUtils::prefixNodeAttrs($img, 'src, srcset,href,dummy', 'p1/');

		$this->assertEquals('p1/img0.jpg', $img->getAttribute('src'));
		$this->assertEquals('p1/img1.jpg 1000w, p1/img2.jpg', $img->getAttribute('srcset'));
		$this->assertEquals('p1/link.html', $img->getAttribute('href'));		
	}

	function test_unprefixNodeAttr() {
		$doc = phpQuery::newDocument('<img src="p1/img0.jpg" srcset="p2/img1.jpg 1000w, p2/img2.jpg" href="p3a/link.html"/>');
		$img = phpQuery::pq('img', $doc)->elements[0];
		$img = HtmlUtils::unprefixNodeAttr($img, 'src', 'p1/');
		$img = HtmlUtils::unprefixNodeAttr($img, 'srcset', 'p2/');
		$img = HtmlUtils::unprefixNodeAttr($img, 'href', 'p3/');

		$this->assertEquals('img0.jpg', $img->getAttribute('src'));
		$this->assertEquals('img1.jpg 1000w, img2.jpg', $img->getAttribute('srcset'));
		$this->assertEquals('p3a/link.html', $img->getAttribute('href'));

		$doc = phpQuery::newDocument('<img src="p1/img0.jpg" srcset="p1/img1.jpg 1000w, p1/img2.jpg" href="p1/link.html"/>');
		$img = phpQuery::pq('img', $doc)->elements[0];
		$test = function($val) {
			return ($val === 'p1/img0.jpg' || $val === 'p1/img2.jpg');
		};
		$img = HtmlUtils::unprefixNodeAttr($img, 'src', 'p1/', $test);
		$img = HtmlUtils::unprefixNodeAttr($img, 'srcset', 'p1/', $test);
		$img = HtmlUtils::unprefixNodeAttr($img, 'href', 'p1/', $test);

		$this->assertEquals('img0.jpg', $img->getAttribute('src'));
		$this->assertEquals('p1/img1.jpg 1000w, img2.jpg', $img->getAttribute('srcset'));
		$this->assertEquals('p1/link.html', $img->getAttribute('href'));				
	}

	function test_unprefixNodeAttrs() {
		$doc = phpQuery::newDocument('<img src="p1/img0.jpg" srcset="p1/img1.jpg 1000w, img2.jpg" href="link.html"/>');
		$img = phpQuery::pq('img', $doc)->elements[0];
		$img = HtmlUtils::unprefixNodeAttrs($img, 'src, srcset,href,dummy', 'p1/');

		$this->assertEquals('img0.jpg', $img->getAttribute('src'));
		$this->assertEquals('img1.jpg 1000w, img2.jpg', $img->getAttribute('srcset'));
		$this->assertEquals('link.html', $img->getAttribute('href'));		
	}	
}