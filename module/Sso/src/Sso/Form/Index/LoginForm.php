<?php
namespace Sso\Form\Index;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct()
    {
		parent::__construct('login-form');
		$this->add(array(
    		'name' => 'loginName',
    		'attributes' => array('type' => 'text'),
    		'options' => array('label' => '登录名')
    	));
		$this->add(array(
			'name' => 'password',
			'attributes' => array('type' => 'password'),
			'options' => array('label' => '密码')
		));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array('type' => 'submit', 'value' => '确认')
		));
	}
}