<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'Sso\IndexController' => 'Sso\Controller\IndexController',
		)
	),
	'router' => array(
		'routes' => array(
			'index' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/',
					'defaults' => array(
						'controller' => 'Sso\IndexController',
						'action' => 'index',
					),
				),
			),
			'register' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/register.json',
					'defaults' => array(
						'controller' => 'Sso\IndexController',
						'action' => 'register',
					)
				),
				'may_terminate' => true,
			),
			'login' => array(
				'type' => 'Literal',
    			'options' => array(
    				'route' => '/login',
    				'defaults' => array(
    					'controller' => 'Sso\IndexController',
    					'action' => 'login',
    				)
    			),
    			'may_terminate' => true,
			),
			'logout' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/logout',
					'defaults' => array(
						'controller' => 'Sso\IndexController',
						'action' => 'logout',
					)
				),
				'may_terminate' => true,
			),
			'read-token' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/read-token.xml',
					'defaults' => array(
    					'controller' => 'Sso\IndexController',
    					'action' => 'read-token',
    				)
				),
				'may_terminate' => true,
    		),
		),
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions'       => true,
		'doctype'                  => 'HTML5',
		'not_found_template'       => 'error/404',
		'exception_template'       => 'error/index',
		'template_map' => array(
			'layout/error'				=> __DIR__ . '/../view/layout/error.phtml',
			'error/404'					=> __DIR__ . '/../view/error/404.phtml',
			'error/index'				=> __DIR__ . '/../view/error/index.phtml',
			'layout/layout'				=> __DIR__ . '/../view/layout/layout.phtml',
			'layout/admin'				=> __DIR__ . '/../view/layout/layout-admin.phtml',
			
			'sso/index/index'			=> __DIR__ . '/../view/sso/index/index.phtml',
			'sso/index/login'			=> __DIR__ . '/../view/sso/index/login.phtml',
			'sso/index/read-token'		=> __DIR__ . '/../view/sso/index/read-token.xml.phtml',
			'sso/index/info'			=> __DIR__ . '/../view/sso/index/info.xml.phtml',
			
			'mail/layout'				=> __DIR__ . '/../view/mail/layout.phtml',
			'mail/user-register'		=> __DIR__ . '/../view/mail/user-register.phtml'
		),
		'strategies' => array(
			'ViewJsonStrategy',
			'ViewFeedStrategy'
		),
	),
);