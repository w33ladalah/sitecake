<?php
ini_set('display_errors','Off');
ini_set('display_warnings', 'Off');
date_default_timezone_set('UTC');


define('SC_ROOT', realpath(__DIR__ . '/../../../'));

define('DRAFT_CONTENT_DIR', SC_ROOT . '/sitecake-content');
define('DRAFT_CONTENT_URL', 'sitecake-content');
define('PUBLIC_IMAGES_DIR', SC_ROOT . '/images');
define('PUBLIC_FILES_DIR', SC_ROOT . '/files');
define('PUBLIC_IMAGES_URL', 'images');
define('PUBLIC_FILES_URL', 'files');
define('SITE_MAP_FILE', SC_ROOT . '/' . 'sitemap.xml');
define('SERVER_BASE', 'sitecake/${version}/server');
define('SERVICE_URL', SERVER_BASE . '/service.php');
define('SITECAKE_EDITOR_LOGIN_URL', 'sitecake/' .
	'${version}/client/publicmanager/publicmanager.nocache.js');
define('SITECAKE_EDITOR_EDIT_URL', 'sitecake/${version}/client/' .
	'contentmanager/contentmanager.nocache.js');
define('CONFIG_URL', 'sitecake/editor.cfg');
define('CREDENTIALS_FILE', realpath(__DIR__ . '/../../credential.php'));
define('TEMP_DIR', SC_ROOT . '/sitecake-content/tmp');

define('SERVER_DIR', realpath(__DIR__));
set_include_path(
	SERVER_DIR . '/application' . PATH_SEPARATOR .
	SERVER_DIR . '/lib'
);

require('vendor/autoload.php');

spl_autoload_register(
	function($className) {
		require(str_replace('_', '/',
				str_replace('\\', '/', ltrim($className, '\\'))) . '.php');
		if(method_exists($className, '__static_init')) {
			$className::__static_init();
		}
	}
);
