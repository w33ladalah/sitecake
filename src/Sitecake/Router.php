<?php
namespace Sitecake;

use Symfony\Component\HttpFoundation\Response;

class Router {
	
	protected $sm;

	protected $services;

	public function __construct($sm, $services) {
		$this->sm = $sm;
		$this->services = $services;
	}

	public function route($req) {
		if (!$req->query->has('service')) {
			$renderer = $this->services['renderer'];
			$page = $req->query->has('page') ? $req->query->get('page') : 'index.html';
			return $this->sm->isLoggedIn() ? 
				$renderer->editResponse($page) : $renderer->loginResponse();
		} else {
			$service = $req->query->get('service');
			$action = $req->query->has('action') ? $req->query->get('action') : null;
			return $this->execute($service, $action, $req);
		}		
	}

	protected function execute($service, $action, $request) {
		if (!isset($this->services[$service]) || 
				!($this->services[$service] instanceof \Sitecake\Services\Service)) {
			return new Response('Invalid service referenced: ' . $service, 400);
		}

		$srv = $this->services[$service];
		if (!$srv->actionExists($action)) {
			return new Response('Invalid action requested: ' . $action, 400);
		}
		
		if ($srv->isAuthRequired($action) && !$this->sm->isLoggedIn()) {
			return new Response('Unauthorized access', 401);
		}

		return $this->response($srv, $action, $request);
	}

	protected function response($service, $action, $request) {
		try {
			$res = $service->$action($request);
			if ($res instanceof Response) {
				return $res;
			} else {
				return new Response($res);
			}
		} catch(\Exception $e) {
			return new Response("Exception: " . $e->getMessage() . "\n\r" . $e->getTraceAsString(), 500);
		}
	}

}
