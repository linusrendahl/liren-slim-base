<?php
/**
 * Routes
 */

//homepage
$app->get('/', '\App\Controllers\Page\CHome:init')->name('home');