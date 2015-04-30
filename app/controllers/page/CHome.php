<?php
namespace App\Controllers\Page;
/**
 * Pagecontroller for Home
 */
class CHome extends \App\Controllers\Page\CPage
{

	public function init()
	{
		
		$data = [];

		$this->app->render('base.php', $data);
	}
	
}