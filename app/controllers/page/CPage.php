<?php
namespace App\Controllers\Page;
/**
 * Pagecontroller for Home
 */
class CPage
{
	
	protected $app;

	function __construct()
	{
		$this->app = \Slim\Slim::getInstance();
	}

}