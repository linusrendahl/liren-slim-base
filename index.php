<?php
/**
 * Liren Slim Base
 * Developed in PHP version 5.6.3
 * @author Linus Rendahl, linus.r@liren.se, www.liren.se
 * @version v1.0
 * @copyright Copyright 2015 Linus Rendahl
 */

//composer packagist
require 'vendor/autoload.php';

//slim application object
$app = new \Slim\Slim([
	'debug'				=> true, //show application errors
	'templates.path' 	=> __DIR__ . '/app/views', //path to view templates
	'mode' 				=> 'dev' //dev OR production (for DB settings etc)
]);

/**
 * Config
 */
require 'config/view.php'; //view data
require 'config/eloquent.php'; //eloquent ORM

/**
 * Routes
 */
require 'app/routes/routes.php';

/**
 * Services
 */
require 'app/services.php';

/**
 * Let's get the party started
 */
$app->run();