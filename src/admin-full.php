<?php
ini_set('display_errors','Off');
ini_set('display_warnings', 'Off');
date_default_timezone_set('UTC');

require('vendor/autoload.php');

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as AdapterLocal;
use League\Flysystem\Adapter\Ftp as AdapterFtp;
use JDesrosiers\Silex\Provider\CorsServiceProvider;

// instantiate Silex application
$app = new Silex\Application();

// An absolute filesystem path to the site root directory (where sc-admin.php is located too).
// It is used only to instantiate the filesystem abstraction. From this point on, all
// paths are relative (to the BASE_DIR) and all paths can be used as relative URLs as well.
$app['BASE_DIR'] = realpath(__DIR__ . '/../../../');

// a path (relative URL) to the CMS working directory (draft, tmp, backup, etc.) 
$app['DRAFT_CONTENT'] = 'sitecake-content';

// a URL relative to sc-admin.php that Sitecake editor is using as the entry point
// to the CMS service API
$app['SERVICE_URL'] = 'sitecake/${version}/server/admin.php';

// a URL relative to sc-admin.php that Sitecake editor is using to load the login module
$app['EDITOR_LOGIN_URL'] = 'sitecake/${version}/client/publicmanager/publicmanager.nocache.js';

// a URL relative to sc-admin.php that Sitecake editor is using to load the editor module
$app['EDITOR_EDIT_URL'] = 'sitecake/${version}/client/contentmanager/contentmanager.nocache.js';

// a URL relative to sc-admin.php that Sitecake editor is using to load the editor configuration
$app['EDITOR_CONFIG_URL'] = 'sitecake/editor.cfg';

// include the server-side configuration that user is expected to modify
include('config.php');

// global error handler
$app->error(function (\LogicException $e, $code) {
    return new Response("Exception: " . $e->getMessage() . "\n\r" . $e->getTraceAsString(), 500);
});

// configure the abstract file system
if ($app['filesystem.adapter'] == 'local') {
	$app['fs'] = $app->share(function($app) {
		return new Filesystem(new AdapterLocal($app['BASE_DIR']));
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

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en'),
));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new Symfony\Component\Translation\Loader\YamlFileLoader());
    $translator->addResource('yaml', __DIR__.'/locales/en.yml', 'en');
    return $translator;
}));

$app['auth'] = $app->share(function($app) {
	return new Sitecake\Auth($app['fs'], '/sitecake/credentials.php');
});

$app['site'] = $app->share(function($app) {
	return new Sitecake\Site($app['fs'], $app);
});

$app['flock'] = $app->share(function($app) {
	return new Sitecake\FileLock($app['fs'], $app['site']->tmpPath());
});

$app['sm'] = $app->share(function($app) {
	return new Sitecake\SessionManager($app['session'], $app['flock'], $app['auth'], $app['site']);
});

$app['renderer'] = $app->share(function($app) {
	return new Sitecake\Renderer($app['site'], $app);
});

$app['services'] = $app->share(function($app) {
	return new Sitecake\Services($app);
});

$app['router'] = $app->share(function($app) {
	return new Sitecake\Router($app['sm'], $app);
});

$hndlr = function(Application $app, Request $request) {
	// check if GD is present
	if (!extension_loaded('gd')) {
		throw new \Exception("GD lib (PHP extension) is required, but it's not loaded.");
	}
	
	$app['services']->load();
	return $app['router']->route($request);
};

$app->match('/', $hndlr)->method("GET");
$app->match('/', $hndlr)->method("POST");

$app->register(new CorsServiceProvider(), array());
$app->after($app["cors"]);

$app->run();
