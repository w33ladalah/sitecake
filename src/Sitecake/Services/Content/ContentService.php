<?php

namespace Sitecake\Services\Content;

use Sitecake\Services\Service;
use Symfony\Component\HttpFoundation\Response;

class ContentService extends Service {

	const SERVICE_NAME = '_content';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	protected $content;

	public function __construct($ctx) {
		$this->ctx = $ctx;
		$this->content = new Content($ctx['site']);
	}

	public function save($request) {
		$id = $request->request->get('scpageid');
		if (is_null($id)) {
			return new Response('Page ID is missing', 400);
		}
		$request->request->remove('scpageid');		
		$this->content->save($request->request->all());
		return $this->json($request, array('status' => 0), 200);		
	}

	public function publish($request) {
		$this->ctx['site']->publishDraft();
		return $this->json($request, array('status' => 0), 200);
	}

}