<?php
namespace Sso;

class Module
{	
    public function getConfig()
    {
    	return include __DIR__ . '/config/module.config.php';
    }
    
	public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__	=> __DIR__ . '/src/' . __NAMESPACE__,
					'Account'		=> __DIR__ . '/src/Account',
					'Aws'			=> BASE_PATH.'/inc/Aws',
					'Guzzle'		=> BASE_PATH.'/inc/Guzzle',
					'Symfony'		=> BASE_PATH.'/inc/Symfony'
				)
            ),
        );
    }
}