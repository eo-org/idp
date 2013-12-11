<?php
namespace Sso\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel, Zend\View\Model\JsonModel, Zend\View\Model\FeedModel;
//use Zend\Json\Json;
use Sso\Form\Index\LoginForm, Sso\Validator, Sso\RemoteUserCookie;
use Account\Document\Token, Account\Document\User;

use Aws\Ses\SesClient;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    	/**
    	 * @todo send email after user register!!
    	 */

    	$csr = new RemoteUserCookie();
    	if($csr->isLogin()) {
    		$userId = $csr->getUserId();
    		$userData = $csr->getUserData();
    		return array(
    			'userId' => $userId,
    			'userData' => $userData
    		);
    	} else {
    		return array(
    			'userId' => null,
    			'userData' => null
    		);
    	}
    }
    
    public function registerAction()
    {
    	$postData = $this->getRequest()->getPost();
    	
    	$loginName = $postData['loginName'];
    	$password = $postData['password'];
    	
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	 
    	$userDoc = $dm->getRepository('Account\Document\User')->findOneByLoginName($loginName);
    	
    	if(is_null($userDoc)) {
    		$userDoc = new User();
    		$userDoc->exchangeArray($postData);
    		$dm->persist($userDoc);
    		$dm->flush();
    		
    		$tokenId = $postData['token'];
    		$jsonModel = new JsonModel(array(
    			'result' => 'success',
    			'userId' => $userDoc->getId(),
    			'userLoginName' => $loginName
    		));
    	} else {
	    	$jsonModel = new JsonModel(array(
	    		'result' => 'fail',
	    		'errorCode' => 'user-existed'
	    	));
    	}
    	return $jsonModel;
    }
    
    public function loginAction()
    {
    	$consumer = $this->params()->fromQuery('consumer');
    	$timeStamp = $this->params()->fromQuery('timeStamp');
    	$tokenId = $this->params()->fromQuery('token');
    	$sig = $this->params()->fromQuery('sig');
    	
    	$loginUrl = $this->params()->fromQuery('loginUrl');
    	$postLoginName = $this->params()->fromQuery('postLoginName');
    	$postLoginPass = $this->params()->fromQuery('postLoginPass');
    	if(empty($consumer) || empty($timeStamp) || empty($tokenId) || empty($sig)) {
    		throw new \Exception('login format error');
    	}
    	
    	$result = Validator::validateLoginUrl($consumer, $timeStamp, $tokenId, $sig);
    	
    	if(!$result) {
    		switch($result) {
    			case 'timeout':
    				throw new \Exception('Request Timeout');
    		}
    		throw new \Exception('Sig Error');
    	}
    	
    	$csr = new RemoteUserCookie();
    	
    	//post data login with encrypted password, may login with another account
    	if(!empty($postLoginName) && !empty($postLoginPass)) {
    		$sm = $this->getServiceLocator();
    		$dm = $sm->get('DocumentManager');
    		$result = $csr->encryptLogin($postLoginName, $postLoginPass, $tokenId, $dm);
    		if($result === true) {
    			$tokenContent = $csr->getUserData();
    			$this->generateToken($tokenId, $tokenContent);
    			header("Location: ".$loginUrl."?tokenReady=ready");
    			exit(0);
    		} else {
    			header("Location: ".$loginUrl."?errorCode=$result&requestLoginName=$postLoginName");
    			exit(0);
    		}
    	}
    	
    	//logged in already, generate new token and go back token consumer
    	if($csr->isLogin()) {
    		$userData = $csr->getUserData();
    		$this->generateToken($tokenId, $userData);
    		header("Location: ".$loginUrl."?tokenReady=ready");
    		exit(0);
    	}
    	
    	//go back to a remote login page
    	if(!empty($loginUrl)) {
    		header("Location: ".$loginUrl);
    		exit(0);
    	}
    	
    	$form = new LoginForm();
    	$errorMsg = array();
    	if($this->getRequest()->isPost()) {
    		$postData = $this->getRequest()->getPost();
    		$form->setData($postData);
    		if($form->isValid()) {
	    		$cookieData = $csr->login($form->getData(), $dm);
	    		if($cookieData === false) {
	    			$errorMsg[] = "用户密码错误";
	    		} else {
	    			$tokenContent = $csr->getUserData();
    				$this->generateToken($tokenId, $tokenContent);
	    			header("Location: ".$loginUrl."?tokenReady=ready");
	    			exit(0);
	    		}
    		}
    	}
    	
    	return array(
    		'form' => $form,
    		'errorMsg' => $errorMsg
    	);
    }
    
    public function readTokenAction()
    {
    	$tokenId = $this->params()->fromPost('token');
    	 
    	$viewModel = new ViewModel();
    	$viewModel->setTerminal(true);
    	if(empty($tokenId)) {
    		$this->getResponse()->setStatusCode(403);
    		$viewModel->setVariables(array(
    			'userId' => null,
    			'userData' => null
    		));
    	} else {
    		$sm = $this->getServiceLocator();
    		$dm = $sm->get('DocumentManager');
    		$tokenDoc = $dm->getRepository('Account\Document\Token')->findOneByTokenId($tokenId);
    		if(!is_null($tokenDoc)) {
    			$this->getResponse()->setStatusCode(200);
    			$userData = $tokenDoc->getUserData();
    			$viewModel->setVariables(array(
    				'userId' => $userData['userId'],
    				'userData' => $userData
    			));
    			$dm->remove($tokenDoc);
    			$dm->flush();
    		} else {
    			$this->getResponse()->setStatusCode(403);
    			$viewModel->setVariables(array());
    		}
    	}
    	
    	return $viewModel;
    }
    
    public function logoutAction()
    {
    	$retUrl = $this->params()->fromQuery('ret');
    	
    	$csr = new RemoteUserCookie();
    	$csr->logout();
    	
    	header("Location: ".$retUrl);
    	exit(0);
    }
    
    protected function generateToken($tokenId, $userData)
    {
    	$sm = $this->getServiceLocator();
    	$dm = $sm->get('DocumentManager');
    	
    	$token = $dm->getRepository('Account\Document\Token')->findOneByTokenId($tokenId);
    	
    	if(is_null($token)) {
    		if(rand(1, 100) <= 4) {
    			$oldDateTime = new \DateTime();
    			$oldDateTime->sub(new \DateInterval('PT12M'));
    			$dm->createQueryBuilder('Account\Document\Token')
	    			->remove()
	    			->field('created')->lte($oldDateTime)
	    			->getQuery()
	    			->execute();
    		}
    		$newToken = new Token();
    		$newToken->exchangeArray(array(
    			'tokenId' => $tokenId,
    			'userData' => $userData
    		));
    		$dm->persist($newToken);
    		$dm->flush();
    	}
    }
}
