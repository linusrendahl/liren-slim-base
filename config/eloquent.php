<?php
/**
 * Boot up Eloquent ORM
 */

if($app->config('mode') === 'production') {
    // Database information (for production environment)
    $settings = array(
        'driver' => 'mysql',
        'host' => '[host]',
        'database' => '[database]',
        'username' => '[username]',
        'password' => '[password]',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'charset' => 'utf8'
    );

} else {
    // Database information (for development server)
    $settings = array(
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'liren-slim-base',
        'username' => 'root',
        'password' => '',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'charset' => 'utf8'
    );
}

// Bootstrap Eloquent ORM
$container = new Illuminate\Container\Container;
$connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
$conn = $connFactory->make($settings);
$resolver = new \Illuminate\Database\ConnectionResolver();
$resolver->addConnection('default', $conn);
$resolver->setDefaultConnection('default');
\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);