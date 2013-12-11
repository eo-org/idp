<?php
namespace Sso\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel, Zend\View\Model\JsonModel;

class ValidateController extends AbstractActionController
{
    public function loginNameAvailableAction()
    {
    	$this->getResponse()->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', 'http://account.fucms.test');
    	
    	$sm = $this->getServiceLocator();
    	$getData = $this->getRequest()->getQuery();
    	$loginName = $getData['loginName'];
    	
    	$result = 'false';
    	if(!empty($loginName)) {
    		$dm = $sm->get('DocumentManager');
    		$user = $dm->getRepository('Account\Document\User')->findOneByLoginName($loginName);
    			
    		if($user == false) {
    			$result = 'true';
    		}
    	}
    	
    	$viewModel = new ViewModel(array('result' => $result));
    	$viewModel->setTerminal(true);
    	return $viewModel;
    }
}