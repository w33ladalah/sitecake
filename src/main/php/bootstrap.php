<?php
ini_set('display_errors','Off');
ini_set('display_warnings', 'Off');
date_default_timezone_set('UTC');

require('vendor/autoload.php');

$app = new Silex\Application();

// include server-side configuration
include('config.php');

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as AdapterLocal;
use League\Flysystem\Adapter\Ftp as AdapterFtp;

// configure the abstract file system
if ($app['filesystem.adapter'] == 'local') {
	$app['fs'] = $app->share(function($app) {
		return new Filesystem(new AdapterLocal(realpath(__DIR__ . '/../../../')));
	});
} else if ($app['filesystem.adapter'] == 'ftp') {
	$app['fs'] = $app->share(function($app) {
		return new Filesystem(new AdapterFtp($app['filesystem.adapter.config']));
	});	
} else {
	dia('Unsupported filesystem.adapter ' + $app['filesystem.adapter'] + '. Supported types are local and ftp. Please check the configuration.');
}

// add application specific filesystem plugins
$app['fs']->addPlugin(new Sitecake\Filesystem\EnsureDirectory);
$app['fs']->addPlugin(new Sitecake\Filesystem\ListPatternPaths);
$app['fs']->addPlugin(new Sitecake\Filesystem\RandomDirectory);
$app['fs']->addPlugin(new Sitecake\Filesystem\CopyPaths);
$app['fs']->addPlugin(new Sitecake\Filesystem\DeletePaths);

$app['env'] = $app->share(function($app) {
	return new Sitecake\Env($app['fs']);
});

$app['renderer'] = $app->share(function($app) {
	return new Sitecake\Renderer();
});

// define constants
define('DRAFT_CONTENT', 'sitecake-content');
define('PUBLIC_IMAGES', 'images');
define('PUBLIC_FILES', 'files');
define('SERVER_BASE', 'sitecake/${version}/server');
define('SERVICE_URL', SERVER_BASE . '/service.php');
define('SITECAKE_EDITOR_LOGIN_URL', 'sitecake/' .
	'${version}/client/publicmanager/publicmanager.nocache.js');
define('SITECAKE_EDITOR_EDIT_URL', 'sitecake/${version}/client/' .
	'contentmanager/contentmanager.nocache.js');
define('CONFIG_URL', 'sitecake/editor.cfg');






