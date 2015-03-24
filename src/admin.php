<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 'Off');
ini_set('display_warnings', 'Off');
date_default_timezone_set('UTC');

$phpVersion = preg_split("/[:.]/", phpversion());
if ( ($phpVersion[0]*10 + $phpVersion[1]) < 54 ) {
	die("PHP version $phpVersion[0].$phpVersion[1] is found on your webhosting.
		PHP version 5.4 (or greater) is required.");
}

function shutdown() {
    $isError = false;

    if ($error = error_get_last()){
    switch($error['type']){
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            $isError = true;
            break;
        }
    }

    if ($isError) {
    	http_response_code(500);
    	echo "Error: ";
        var_dump ($error);
    }
}
register_shutdown_function('shutdown');

include('admin-full.php');