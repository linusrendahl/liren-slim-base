<?php
/**
 * Services in DI Container
 */

//Authentication controller
$app->container->singleton('auth', function () use ($app) {
	return new \App\Controllers\AuthController($app);
});

//Usercontroller
$app->container->singleton('user', function () use ($app) {
	return new \App\Controllers\UserController($app);
});

//MailController
$app->container->singleton('mail', function () use ($app) {

	$transport = [
		'smtp'		=> 'mailout.one.com',
		'port'		=> 25,
		'email'		=> 'site@dubetydermer.se',
		'password'	=> 'RdAF5pV2I2yx'
	];

	$mail = new \App\Controllers\MailController($app);
	$mail->setTransport($transport);
	return $mail;
});