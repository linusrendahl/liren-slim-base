<?php
/**
 * Set default variables for Views
 */

//Make variables available in view on default
$app->hook('slim.before.router', function () use ($app) {
    
    //make slim application object available in views
    $app->view->setData('app', $app);

});