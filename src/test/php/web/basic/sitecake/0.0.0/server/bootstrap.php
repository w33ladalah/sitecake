<?php
ini_set('display_errors','On');
ini_set('display_warnings', 'On');
date_default_timezone_set('UTC');

$phpVersion = preg_split("/[:.]/", phpversion());
if ( ($phpVersion[0]*10 + $phpVersion[1]) < 53 ) {
	die("PHP version $phpVersion[0].$phpVersion[1] is found on your webhosting.
		PHP version 5.3 (or greater) is required.");
}

define('SC_ROOT', realpath(__DIR__ . '/../../../.'));

define('DRAFT_CONTENT_DIR', SC_ROOT . '/sitecake-content');
define('DRAFT_CONTENT_URL', 'sitecake-content');
define('PUBLIC_IMAGES_DIR', SC_ROOT . '/images');
define('PUBLIC_FILES_DIR', SC_ROOT . '/files');
define('PUBLIC_IMAGES_URL', 'images');
define('PUBLIC_FILES_URL', 'files');
define('SITE_MAP_FILE', SC_ROOT . '/' . 'sitemap.xml');

define('SERVICE_URL', 'sitecake/0.0.0/server/service.php');
define('SITECAKE_EDITOR_LOGIN_URL',
	'sitecake/0.0.0/client/publicmanager/publicmanager.nocache.js');
define('SITECAKE_EDITOR_EDIT_URL', 'sitecake/0.0.0/client/' .
	'contentmanager/contentmanager.nocache.js');
define('CONFIG_URL', 'sitecake/editor.cfg');
define('CREDENTIALS_FILE', realpath(__DIR__ . '/../../credential.php'));
define('TEMP_DIR', SC_ROOT . '/sitecake-content/tmp');

define('SERVER_DIR', realpath(__DIR__ . '/../../../../../../../src/main/php'));
set_include_path(
	SERVER_DIR . '/application' . PATH_SEPARATOR .
	SERVER_DIR . '/../lib'
);

spl_autoload_register(
	function($className) {
		require(str_replace('_', '/',
			str_replace('\\', '/', ltrim($className, '\\'))) . '.php');
		if(method_exists($className, '__static_init')) {
			$className::__static_init();
		}
	}
);
