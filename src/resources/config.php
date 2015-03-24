<?php

// If the PHP process on the server has permission to write to the website
// root files (e.g. to delete/update html files, to create and delete folders)
// use 'local' filesystem adapter.
$app['filesystem.adapter'] = 'local';

// If the PHP process on the server doesn't have permission to write to the website
// root files then use the 'ftp' adapter and provide necessary FTP access properties.
// FTP protocol will be used to manage the website root files.
/*
$app['filesystem.adapter'] = 'ftp';
$app['filesystem.adapter.config'] = array(
    // optional config settings
    //'port' => 21,
    //'root' => '/path/to/root',
    //'passive' => true,
    //'ssl' => true,
    //'timeout' => 30,

    'host' => 'ftp.example.com',
    'username' => 'username',
    'password' => 'password',
    'port' => 21
);
*/

// the number of the recent site versions to be kept
$app['site.number_of_backups'] = 2;

// a list of image widths in pixels that would be used for generating
// images for srcset 
$app['image.srcset_widths'] = array(1280, 960, 640, 320);
// a max relative diff (in percents) between two image widths in pixels
// so they could be considered similar
$app['image.srcset_width_maxdiff'] = 20;

// CSS selector for an element that contains site top nav menu.
// If it selects more than one element, all menue elements will have the same content
$app['pages.nav.container_selector'] = 'nav.sc-nav';
// The top nav item CSS selector
$app['pages.nav.item_selector'] = 'nav.sc-nav:first a';
// The top nav item template
$app['pages.nav.item'] = '<a class="${active}" href="${url}">${title}</a>';

// An alternative nav configuration example, using <ul> tag as the container and <li> tags
// for menu items
//$app['pages.nav.container_selector'] = 'ul.sc-nav';
//$app['pages.nav.item_template'] = '<li class="${active}"><a href="${url}">${title}</a></li>';