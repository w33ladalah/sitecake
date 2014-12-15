<?php

namespace Sitecake;

use \phpQuery;

class PageTest extends \PHPUnit_Framework_TestCase {
	
	public function test_isResourceUrl() {
		$this->assertTrue(Page::isResourceUrl('files/doc-sc123456789abcd.doc'));
		$this->assertTrue(Page::isResourceUrl('images/image-2-sc123456789abcd-00.jpg'));
		$this->assertFalse(Page::isResourceUrl('#files/doc-sc123456789abcd.doc'));
		$this->assertFalse(Page::isResourceUrl('javascript:files/doc-sc123456789abcd-00.doc'));
		$this->assertFalse(Page::isResourceUrl('http://some.come/files/doc-sc123456789abcd.doc'));
	}

	public function test_prefixResourceUrls() {
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

	public function test_unprefixResourceUrls() {
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

	public function test_listResourceUrls() {
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

		$urls = $page->listResourceUrls();

		$this->assertEquals(3, count($urls));
		$this->assertEquals('p/files/a3-sc123456789abcd.doc', $urls[0]);
		$this->assertEquals('p/images/img1-sc123456789abcd-00.jpg', $urls[1]);
		$this->assertEquals('draft/images/img1-sc123456789abcd-00.jpg', $urls[2]);
	}

	public function test_render() {
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

	public function test_pageId() {
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

	public function test_ensurePageId() {
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

	public function test_removePageId() {
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

}