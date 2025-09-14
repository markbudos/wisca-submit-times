<?php

class Session {

	public static $ADMIN = 1;
	public static $MEMBER = 2;
	public static $REG = 3;
	public static $WWW = 4;
	
	public static function getSession($forceFresh=false) {

		static $session = null;

		if (!$session) {
			$session = new Session();
			$session->init();
		}
		
		return $session;
	}

	public function init() {	
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
		if (isset($_REQUEST['email']) && isset($_REQUEST['password']) && $type != 'new') {
			$this->user = User::loadUserByEmailPass($_REQUEST['email'], $_REQUEST['password']);
		} else if (!$this->user) {
			$this->user = User::loadUserByCookie();
		}
	}

	public function reset() {
		$this->user = User::loadUserByCookie();
	}

	public function checkUser($type = null) {
		if (isset($_SERVER['HTTP_HOST']) && !strstr($_SERVER['HTTP_HOST'], 'www') && substr_count($_SERVER['HTTP_HOST'], '.') == 1) {
			header("Location: http://www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			die();
		}
		if ($type == self::$MEMBER && !$this->user->member) {
			header("Location: account.php?rd=".$_SERVER['SCRIPT_URL']."&msg=Must+be+a+member+for+access");
			die();
		} else if ($type == self::$ADMIN && !$this->user->admin) {
			header("Location: account.php?rd=".$_SERVER['SCRIPT_URL']."&msg=Must+be+a+admin+for+access");
			die();
		} else if ($type == self::$REG && !$this->user->email) {
			header("Location: account.php?msg=Must+be+registered+for+access");
			die();
		}
	}

	public $user;

}

Session::getSession();

?>