<?php
namespace Sso;

class Validator
{
	const SERVICE_CMS_KEY = 'zvmiopav7BbuifbahoUifbqov541huog5vua4ofaweafeq98fvvxreqh';
	const SERVICE_ACCOUNT_KEY = 'nfieawfueau86572hhuiGYU615hf678tRcewq7uh43qffugUIGIfefwg';
	const SERVICE_FILE_KEY = 'gioqnfieowhczt7vt87qhitonqfn8eaw9y8s90a6fnvuzioguifeb';
	const SERVICE_FORM_KEY = '21FguiogaLL9y923t715hi4guo32iofgdsz8ohj0phgyUIFMUubNUh78rF';
	
	public static function validateLoginUrl($consumer, $timeStamp, $token, $sig)
	{
		$serverTime = time();
		
		if($serverTime - $timeStamp > 1800) {
			return 'timeout';
		}
		
		switch($consumer) {
			case 'cms':
				$sigGenerated = md5($consumer.$timeStamp.$token.self::SERVICE_CMS_KEY);
				break;
			case 'service-account':
				$sigGenerated = md5($consumer.$timeStamp.$token.self::SERVICE_ACCOUNT_KEY);
				break;
			case 'service-file':
				$sigGenerated = md5($consumer.$timeStamp.$token.self::SERVICE_FILE_KEY);
				break;
			case 'service-form':
				$sigGenerated = md5($consumer.$timeStamp.$token.self::SERVICE_FORM_KEY);
				break;
		}
	
		if($sigGenerated == $sig) {
			return true;
		}
		return false;
	}
}