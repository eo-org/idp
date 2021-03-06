<?php
namespace Sso;

use Zend\Json\Json;
use Sso\Validator;
use Account\Document\User;

class CookieUser
{
	private static $_md5salt = 'fie&4Jgoaaq1d#$@(lj21';
	private static $_md5salt2 = '6234GY69)+3jo108';
	
	protected $userId;
	protected $userLoginName;
	protected $userRoleId;
	
	public function __construct()
	{
		if($this->isLogin()) {
			$this->userId = $_COOKIE['id'];
			$this->userLoginName = $_COOKIE['loginName'];
			$this->userRoleId = $_COOKIE['roleId'];
		}
	}
	
	public function login($post, $dm)
	{
		$loginName = $post['loginName'];
		$password = $post['password'];
		
		$userDoc = $dm->createQueryBuilder('Account\Document\User')
			->field('loginName')->equals($loginName)
			->field('password')->equals($password)
			->getQuery()
			->getSingleResult();
		
		if(!is_null($userDoc)) {
			$this->userId = $userDoc->getId();
			$this->userLoginName = $userDoc->getLoginName();
			$this->userRoleId = $userDoc->getRoleId();
			
			$cookieData = $this->getUserData();
			$this->_updateCookie($cookieData);
			$this->_isLogin = true;
			return true;
		} else {
			return false;
		}
	}
	
	public function encryptLogin($loginName, $encryptPass, $salt, $dm)
	{
		$userDoc = $dm->createQueryBuilder('Account\Document\User')
			->field('loginName')->equals($loginName)
			->getQuery()
			->getSingleResult();
	
		if(!is_null($userDoc)) {
			$pass = $userDoc->getPassword();
			$apiKey = Validator::SERVICE_CMS_KEY;
			if($encryptPass != md5($salt.$loginName.$pass.$apiKey)) {
				return 'password-not-match';
			}
			
			$this->userId = $userDoc->getId();
			$this->userLoginName = $userDoc->getLoginName();
			$this->userRoleId = $userDoc->getRoleId();
			
			$cookieData = $this->getUserData();
			$this->_updateCookie($cookieData);
			$this->_isLogin = true;
			return true;
		} else {
			return 'user-not-found';
		}
	}
	
	public function logout()
	{
		setcookie('userId', '', 1, '/');
		setcookie('startTimeStamp', '', 1, '/');
		setcookie('liv', '', 1, '/');
		$this->_isLogin = false;
	}

	public function isLogin()
	{
		if(isset($_COOKIE['id']) && $_COOKIE['id'] != '') {
			$livToken = md5(self::$_md5salt.$_COOKIE['id'].self::$_md5salt2.$_COOKIE['startTimeStamp']);
			if($livToken == $_COOKIE['liv']) {
				$isLogin = true;
			} else {
				$isLogin = false;
			}
		} else {
			$isLogin = false;
		}
		return $isLogin;
	}

	public function getUserId()
	{
		return $this->userId;
	}
	
	public function getUserLoginName()
	{
		return $this->userLoginName;
	}
	
	public function getUserData()
	{
		return array(
			'id' => $this->userId,
			'loginName' => $this->userLoginName,
			'roleId' => $this->userRoleId,
			'permissions' => '*'
		);
	}
	
	public function _updateCookie($cookieContent)
	{
		//two weeks is 1209600 = 3600 * 24 * 14
		foreach($cookieContent as $k => $v) {
			setcookie($k, $v, time()+1209600, '/');
		}
		
		$startTimeStamp = time();
		$liv = md5(self::$_md5salt.$this->userId.self::$_md5salt2.$startTimeStamp);
		
		setcookie('startTimeStamp', $startTimeStamp, time()+1209600, '/');
		setcookie('liv', $liv, time()+1209600, '/');
		
	}
}