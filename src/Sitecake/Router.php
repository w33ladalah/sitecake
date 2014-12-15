<?php
namespace Sitecake;

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
				$renderer->editResponse() : $renderer->loginResponse();
		} else {
			$service = $req->query->get('service');
			$action = $req->query->has('action') ? $req->query->get('action') : null;
			return $this->execute($service, $action, $req);
		}		
	}

	protected function execute($service, $action, $request) {
		if (!isset($this->services[$service]) || 
				!($this->services[$service] instanceof \Sitecake\Services\Service)) {
			return 4044;
		}

		$srv = $this->services[$service];
		if (!$srv->actionExists($action)) {
			return 402;
		}
		
		if ($srv->isAuthRequired($action) && !$this->sm->isLoggedIn()) {
			return 401;
		}

		return $srv->$action($request);
	}

}
