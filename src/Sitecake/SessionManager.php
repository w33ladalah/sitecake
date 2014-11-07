<?php

namespace Sitecake;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionManger implements SessionManagerInterface {
	
	protected $session;

	protected $fileLock;

	protected $auth;

	public function __construct(SessionInterface $session, FileLock $fileLock, AuthInterface $auth) {
		$this->session = $session;
		$this->fileLock = $fileLock;
		$this->auth = $auth;
	}

	/**
	 * Checks if the current user is logged in.
	 * 
	 * @return boolean returns true if user is logged in.
	 */
	public function isLoggedIn() {
		return $this->session->has('loggedin');
	}

	public function login($credentials) {
		if ($this->auth->authenticate($credentials)) {
			if ($this->fileLock->exists('login')) {
				return 2;
			} else {
				$this->session->set('loggedin', true);
				$this->fileLock->set('login', 20);
				return 0;
			}
		} else {
			return 1;
		}
	}

	public function logout() {
		$this->session->invalidate(0);
		$this->fileLock->remove('login');		
	}

	public function alive() {
		if ($this->isLoggedIn()) {
			$this->fileLock->set('login', 20);
		}
	}

}