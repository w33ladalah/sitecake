<?php
ini_set('display_errors','Off');
ini_set('display_warnings', 'Off');
date_default_timezone_set('UTC');

require('vendor/autoload.php');

$app = new Silex\Application();

// include server-side configuration
include('config.php');

$app['DRAFT_CONTENT'] = 'sitecake-content';
$app['PUBLIC_IMAGES'] = 'images';
$app['PUBLIC_FILES'] = 'files';
$app['SERVER_BASE'] = 'sitecake/${version}/server';
$app['SERVICE_URL'] = SERVER_BASE . '/admin.php';
$app['SITECAKE_EDITOR_LOGIN_URL'] = 'sitecake/' .
	'${version}/client/publicmanager/publicmanager.nocache.js';
$app['SITECAKE_EDITOR_EDIT_URL'] = 'sitecake/${version}/client/' .
	'contentmanager/contentmanager.nocache.js';
$app['CONFIG_URL'] = 'sitecake/editor.cfg';

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

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en'),
));
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new Symfony\Component\Translation\Loader\YamlFileLoader());
    $translator->addResource('yaml', __DIR__.'/locales/en.yml', 'en');
    return $translator;
}));

$app['env'] = $app->share(function($app) {
	return new Sitecake\Env($app['fs']);
});

$app['renderer'] = $app->share(function($app) {
	return new Sitecake\Renderer($app['fs'], $app);
});

$env = $app['env'];
$app['debug'] = true;
$app->run();
