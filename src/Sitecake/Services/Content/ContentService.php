<?php

namespace Sitecake\Services\Content;

use Sitecake\Services\Service;

class ContentService extends Service {

	const SERVICE_NAME = '_content';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	protected $content;

	public function __construct($ctx) {
		$this->ctx = $ctx;
		$this->content = new Content();
	}

	public function save($request) {
		$credentials = $request->query->get('credentials');
		$status = $this->ctx['sm']->save();
		return $this->json($request, array('status' => $status), 200);		
	}

}