<?php

namespace Sitecake;

interface AuthInterface {
	public function authenticate($credentails);

	public function setCredentials($credentails);
}