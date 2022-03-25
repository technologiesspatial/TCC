<?php
define('ENVIRONMENT',"development");
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	break;
	
	case 'testing':
	case 'production':
		error_reporting(0);
		ini_set('display_errors', 0);
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

use Zend\Mvc\Application;
use Zend\Stdlib\ArrayUtils;


date_default_timezone_set("America/Los_Angeles");
chdir(dirname(__DIR__));

defined('ROOT_PATH') || define('ROOT_PATH', realpath(dirname(__FILE__). ''));

require_once "vendor/constants.php";
require_once "vendor/db-definers.php";
require_once "vendor/functions.php";
require_once "vendor/public_access.php";
require_once "vendor/site_assets.php";

// Decline static file requests back to the PHP built-in webserver
if(php_sapi_name() === 'cli-server') {
	$path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
	if (__FILE__ !== $path && is_file($path)) {
		return false;
	}
	unset($path);
}

// Composer autoloading
include ROOT_PATH. '/vendor/autoload.php';

if (! class_exists(Application::class)){
	throw new RuntimeException(
		"Unable to load application.\n"
		. "- Type `composer install` if you are developing locally.\n"
		. "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
		. "- Type `docker-compose run zf composer install` if you are using Docker.\n"
	);
}

// Retrieve configuration
$appConfig = require ROOT_PATH.'/config/application.config.php';
if (file_exists(__DIR__ . '/../config/development.config.php')) {
	$appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}

// Run the application!
if(!defined('_CRONJOB_') || _CRONJOB_ == false)
{
// Run the application!
Application::init($appConfig)->run();
}