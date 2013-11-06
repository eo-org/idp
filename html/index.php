<?php
chdir(dirname(__DIR__));

define("BASE_PATH", getenv('BASE_PATH'));

include BASE_PATH.'/inc/Zend/Loader/StandardAutoloader.php';

$autoLoader = new Zend\Loader\StandardAutoloader(array(
    'namespaces' => array(
        'Zend'		=> BASE_PATH.'/inc/Zend',
    	'Core'		=> BASE_PATH.'/inc/Core',
    	'Brick'		=> BASE_PATH.'/extension/Brick',
    	'Doctrine'	=> BASE_PATH.'/inc/Doctrine',
    	'Account'	=> BASE_PATH.'/service-account/module/Account'
    ),
	'prefixes' => array(
		'Twig'	=> BASE_PATH.'/inc/Twig',
	)
));
$autoLoader->register();

$application = Zend\Mvc\Application::init(include 'config/application.config.php')->run();
$finishTime = microtime();