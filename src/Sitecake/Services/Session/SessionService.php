<?php

namespace Sitecake\Services\Session;

use Symfony\Component\HttpFoundation\Response;
use Sitecake\Services\Service;

class SessionService extends Service {

	const SERVICE_NAME = '_session';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	public function __construct($ctx) {
		$this->ctx = $ctx;
	}

	public function isAuthRequired($action) {
		return !($action === 'login' || $action === 'change');
	}

	public function login($request) {
		$credentials = $request->query->get('credentials');
		$status = $this->ctx['sm']->login($credentials);
		return $this->json($request, array('status' => $status), 200);
	}

	public function change($request) {
		$credentials = $request->query->get('credentials');
		$newCredentials = $request->query->get('newCredentials');

		if (is_null($credentials) || is_null($newCredentials)) {
			return new Response(null, 400);
		}

		if ($this->ctx['auth']->authenticate($credentials)) {
			$this->ctx['auth']->setCredentials($newCredentials);
			$status = 0;
		} else {
			$status = 1;
		}
		return $this->json($request, array('status' => $status), 200);	
	}

	public function logout($request) {
		$this->ctx['sm']->logout();
		return $this->json($request, array('status' => 0), 200);
	}

	public function alive($request) {
		$this->ctx['sm']->alive();
		return $this->json($request, array('status' => 0), 200);
	}
}