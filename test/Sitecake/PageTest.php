<?php

namespace Sitecake;

use \phpQuery;

class PageTest extends \PHPUnit_Framework_TestCase {

	function test_prefixResourceUrls() {
		$html = '<html><head></head><body>'.
			'<div>'.
				'<a id="a1" href="a1">a1</a>'.
			'</div>'.
			'<div class="sc-content-1">'.
				'<a id="a2" href="a2">a2</a>'.
			'</div>'.
			'<div class="sc-content">'.
				'<a id="a3" href="files/a3-sc123456789abcd.doc">a3</a>'.
				'<img id="img1" src="images/img1-sc123456789abcd-00.jpg" srcset="draft/images/img1-sc123456789abcd-00.jpg 1, img2.jpg 2"/>'.
			'</div>'.
			'<div class="sc-content sc-content-2">'.
				'<a id="a4" href="javascript:files/a4-sc123456789abcd.doc">a4</a>'.
				'<a id="a5" href="#images/a5-sc123456789abcd.doc">a5</a>'.
				'<a id="a6" href="https://files/a6-sc123456789abcd.doc">a6</a>'.				
			'</div>'.
			'</body></html>';

		$page = new Page($html);

		$page->prefixResourceUrls('p/');

		$doc = phpQuery::newDocument((string)$page);

		$this->assertEquals('a1', phpQuery::pq('#a1', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('a2', phpQuery::pq('#a2', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('p/files/a3-sc123456789abcd.doc', phpQuery::pq('#a3', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('javascript:files/a4-sc123456789abcd.doc', phpQuery::pq('#a4', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('#images/a5-sc123456789abcd.doc', phpQuery::pq('#a5', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('https://files/a6-sc123456789abcd.doc', phpQuery::pq('#a6', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('p/images/img1-sc123456789abcd-00.jpg', phpQuery::pq('#img1', $doc)->elements[0]->getAttribute('src'));
		$this->assertEquals('p/draft/images/img1-sc123456789abcd-00.jpg 1, img2.jpg 2', phpQuery::pq('#img1', $doc)->elements[0]->getAttribute('srcset'));
	}

	function test_unprefixResourceUrls() {
		$html = '<html><head></head><body>'.
			'<div>'.
				'<a id="a1" href="p/a1">a1</a>'.
			'</div>'.
			'<div class="sc-content-1">'.
				'<a id="a2" href="p/a2">a2</a>'.
			'</div>'.
			'<div class="sc-content">'.
				'<a id="a3" href="p/files/a3-sc123456789abcd.doc">a3</a>'.
				'<img id="img1" src="p/images/img1-sc123456789abcd-00.jpg" srcset="draft/images/img1-sc123456789abcd-00.jpg 1, p/img2.jpg 2"/>'.
			'</div>'.
			'<div class="sc-content sc-content-2">'.
				'<a id="a4" href="javascript:p/files/a4-sc123456789abcd.doc">a4</a>'.
				'<a id="a5" href="#p/images/a5-sc123456789abcd.doc">a5</a>'.
				'<a id="a6" href="https://p/files/a6-sc123456789abcd.doc">a6</a>'.				
			'</div>'.
			'</body></html>';

		$page = new Page($html);

		$page->unprefixResourceUrls('p/');

		$doc = phpQuery::newDocument((string)$page);

		$this->assertEquals('p/a1', phpQuery::pq('#a1', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('p/a2', phpQuery::pq('#a2', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('files/a3-sc123456789abcd.doc', phpQuery::pq('#a3', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('javascript:p/files/a4-sc123456789abcd.doc', phpQuery::pq('#a4', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('#p/images/a5-sc123456789abcd.doc', phpQuery::pq('#a5', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('https://p/files/a6-sc123456789abcd.doc', phpQuery::pq('#a6', $doc)->elements[0]->getAttribute('href'));
		$this->assertEquals('images/img1-sc123456789abcd-00.jpg', phpQuery::pq('#img1', $doc)->elements[0]->getAttribute('src'));
		$this->assertEquals('draft/images/img1-sc123456789abcd-00.jpg 1, p/img2.jpg 2', phpQuery::pq('#img1', $doc)->elements[0]->getAttribute('srcset'));
	}

	function test_listResourceUrls() {
		$html = '<html><head></head><body>'.
			'<div>'.
				'<a id="a1" href="p/a1">a1</a>'.
			'</div>'.
			'<div class="sc-content-1">'.
				'<a id="a2" href="p/a2">a2</a>'.
			'</div>'.
			'<div class="sc-content">'.
				'<a id="a3" href="p/files/a3-sc123456789abcd.doc">a3</a>'.
				'<img id="img1" src="p/images/img1-sc123456789abcd-00.jpg" srcset="draft/images/img1-sc123456789abcd-00.jpg 451w,draft/images/img2-sc123456789abcd-00.jpg 300w, p/img2.jpg 200w"/>'.
			'</div>'.
			'<div class="sc-content sc-content-2">'.
				'<a id="a4" href="javascript:p/files/a4-sc123456789abcd.doc">a4</a>'.
				'<a id="a5" href="#p/images/a5-sc123456789abcd.doc">a5</a>'.
				'<a id="a6" href="https://p/files/a6-sc123456789abcd.doc">a6</a>'.				
			'</div>'.
			'</body></html>';

		$page = new Page($html);

		$urls = $page->listResourceUrls();

		$this->assertEquals(4, count($urls));
		$this->assertEquals('p/files/a3-sc123456789abcd.doc', $urls[0]);
		$this->assertEquals('p/images/img1-sc123456789abcd-00.jpg', $urls[1]);
		$this->assertEquals('draft/images/img1-sc123456789abcd-00.jpg', $urls[2]);
		$this->assertEquals('draft/images/img2-sc123456789abcd-00.jpg', $urls[3]);
		
	}

	function test_render() {
		$html = '<html><head></head><body>'.
			'<ul class="sc-nav">'.
				'<li><a id="a1" href="http://absolute.com">n1</a></li>'.
				'<li><a id="a2" href="index.html">n1</a></li>'.
				'<li><a id="a3" href="contact.html">n1</a></li>'.
				'<li><a id="a4" href="somewhere/page.html">n1</a></li>'.
				'<li><a id="a5" href="/something.html">n1</a></li>'.
			'</ul>'.
			'<div class="c1 sc-content">'.
			'</div>'.
			'<div class="c2 sc-content-2">'.
			'</div>'.
			'<div class="c3 sc-repeater-1">'.
			'</div>'.
			'</body></html>';

		$page = new Page($html);
		$op = phpQuery::newDocument($page->render());

		$this->assertEquals('http://absolute.com', phpQuery::pq('#a1')->elements[0]->getAttribute('href'));
		$this->assertEquals('sc-admin.php?page=index.html', phpQuery::pq('#a2')->elements[0]->getAttribute('href'));
		$this->assertEquals('sc-admin.php?page=contact.html', phpQuery::pq('#a3')->elements[0]->getAttribute('href'));
		$this->assertEquals('somewhere/page.html', phpQuery::pq('#a4')->elements[0]->getAttribute('href'));
		$this->assertEquals('/something.html', phpQuery::pq('#a5')->elements[0]->getAttribute('href'));
	}

	function test_pageId() {
		$html1 = '<html><head>'.
			'<meta name="application-name" content="sitecake" data-pageid="1234567890abcdefgh"/>'.
			'</head><body>'.
			'</body></html>';

		$page = new Page($html1);
		$this->assertEquals('1234567890abcdefgh', $page->pageId());

		$html2 = '<html><head>'.
			'<meta name="application-name" content="sitecake" data-id="1234567890abcdefgh"/>'.
			'</head><body>'.
			'</body></html>';

		$page = new Page($html2);
		$this->assertNull($page->pageId());

		$html3 = '<html><head>'.
			'<meta name="application-name" content="sitecake"/>'.
			'</head><body>'.
			'</body></html>';

		$page = new Page($html3);
		$this->assertNull($page->pageId());		
	}

	function test_ensurePageId() {
		$html = '<html><head>'.
			'</head><body>'.
			'</body></html>';

		$page = new Page($html);		
		$page->ensurePageId();		
		$o = phpQuery::newDocument((string)$page);
		$this->assertEquals(1, phpQuery::pq("meta[content='sitecake']")->count());
		$this->assertEquals(1, preg_match('/.+/', phpQuery::pq("meta[content='sitecake']")->get(0)->getAttribute('data-pageid')));

		$html = '<html><head>'.
			'<meta name="application-name" content="sitecake"/>'.
			'</head><body>'.
			'</body></html>';

		$page = new Page($html);		
		$page->ensurePageId();
		$o = phpQuery::newDocument((string)$page);
		$this->assertEquals(1, phpQuery::pq('meta[data-pageid]')->count());
		$this->assertEquals(1, preg_match('/.+/', phpQuery::pq('meta[data-pageid]')->get(0)->getAttribute('data-pageid')));

	}

	function test_removePageId() {
		$html = '<html><head>'.
			'<meta name="application-name" content="sitecake" data-pageid="1234567890abcdefgh"/>'.
			'</head><body>'.
			'</body></html>';

		$page = new Page($html);		
		$page->removePageId();
		$o = phpQuery::newDocument((string)$page);
		$this->assertEquals(0, phpQuery::pq('meta[data-pageid]')->count());	
		$this->assertEquals(1, phpQuery::pq("meta[content='sitecake']")->count());	

		$html = '<html><head>'.
			'<meta name="application-name" content="app"/>'.
			'</head><body>'.
			'</body></html>';

		$page = new Page($html);		
		$page->removePageId();
		$o = phpQuery::newDocument((string)$page);	
		$this->assertEquals(1, phpQuery::pq("meta[content='app']")->count());		
	}

	function test_toString() {
		$html = '<html><head>'.
			'</head><body>'.
			'</body></html>';

		$page = new Page($html);
		$o = $page->__toString();

		$this->assertTrue(is_string($o));		
	}

	function test_setContainerContent() {
		$html = '<html><head>'.
			'</head><body>'.
			'<div class="block sc-content-cnt1"></div>'.
			'</body></html>';

		$page = new Page($html);

		$page->setContainerContent('cnt1', '<p>test_content</p>');
		$o = (string)$page;

		$this->assertTrue(strpos($o, 'test_content') !== -1);
	}

	function test_containers() {
		$html = '<html><head>'.
			'</head><body>'.
			'<div class="block sc-content-cnt1"></div>'.
			'<div class=" sc-content-cnt2 "></div>'.
			'<div class="sc-content-cnt3"></div>'.
			'<div class="blocksc-content-cnt4"></div>'.
			'<div class="blocksc-content- cnt5"></div>'.
			'<div class="sc-content"></div>'.			
			'</body></html>';

		$page = new Page($html);
		$cnts = $page->containers();

		$this->assertTrue(is_array($cnts));
		$this->assertEquals(3, count($cnts));
		$this->assertTrue(in_array('cnt1', $cnts));		
		$this->assertTrue(in_array('cnt2', $cnts));		
		$this->assertTrue(in_array('cnt3', $cnts));		
	}

	function test_normalizeContainerNames() {
		$html = '<html><head>'.
			'</head><body>'.
			'<div class="sc-content-cnt1"></div>'.
			'<div class="sc-content"></div>'.
			'<div class="sc-content-"></div>'.			
			'</body></html>';

		$page = new Page($html);
		$page->normalizeContainerNames();
		$cnts = $page->containers();

		$this->assertTrue(is_array($cnts));
		$this->assertEquals(2, count($cnts));
		$this->assertTrue(in_array('cnt1', $cnts));		
		$this->assertEquals(0, strpos($cnts[1], '_cnt_'));					
	}

	function test_cleanupContainerNames() {
		$html = '<html><head>'.
			'</head><body>'.
			'<div class="sc-content-cnt1"></div>'.
			'<div class="sc-content-_cnt_12345"></div>'.
			'<div class="test sc-content-_cnt_abcd some"></div>'.
			'<div class="sc-content-"></div>'.			
			'</body></html>';

		$page = new Page($html);
		$page->cleanupContainerNames();
		$cnts = $page->containers();

		$this->assertTrue(is_array($cnts));
		$this->assertEquals(1, count($cnts));
		$this->assertTrue(in_array('cnt1', $cnts));							
	}

	function test_adjustNavLinks() {
		$html = '<html><head>'.
			'</head><body>'.
			'<ul class="sc-nav">'.
			'<li><a id="l1" href="about.html">link</a></li>'.
			'<li><a id="l2" href="/doc.html"></a></li>'.
			'<li><a id="l3" href="http://google.com"></a></li>'.
			'</ul>'.
			'<a id="l4" href="contact.html"></a>'.
			'<div class="sc-content-cnt1"><a id="l5" href="home.html"></a></div>'.		
			'</body></html>';

		$page = new Page($html);
		$p = $page->render();

		$this->assertEquals('sc-admin.php?page=about.html', phpQuery::pq('#l1', $p)->elements[0]->getAttribute('href'));
		$this->assertEquals('/doc.html', phpQuery::pq('#l2', $p)->elements[0]->getAttribute('href'));
		$this->assertEquals('http://google.com', phpQuery::pq('#l3', $p)->elements[0]->getAttribute('href'));
		$this->assertEquals('sc-admin.php?page=contact.html', phpQuery::pq('#l4', $p)->elements[0]->getAttribute('href'));
		$this->assertEquals('sc-admin.php?page=home.html', phpQuery::pq('#l5', $p)->elements[0]->getAttribute('href'));
	}	

}