<?php 
class User {

	public $userId;
	public $name;
	public $email;
	public $member;
	public $admin;
	public $created;
	public $modified;
	public $guid;
	public $newpassword; //usually null
	public $loginState; //0=garbage, 1=passive, 2=active
	public $deleted = false;
	public $affiliation;

	public static function loadUserByUserId($userId) {
		$conn = WiscaDB::get();
		$result =& $conn->query("select * from Users where userId = ?", array($userId));
		if ($row =& $result->fetchRow()) {
			$user = new User();
			$user->init($row);
		}
		$conn->disconnect();
 		return $user;								
	}

	public static function loadUserByCookie() {
		$conn = WiscaDB::get();
		$setsession = true;
		if (isset($_COOKIE['session'])) {
			$tmp = self::makeCookie();				
			if ($tmp == $_COOKIE['session']) {
				$setsession = false;
			} else {
				setcookie('session', '', time() - 3600);
			}
		} 
		$guid = null;
		$user = null;
		if (isset($_COOKIE['guid'])) {
			$guid = $_COOKIE['guid'];
			$result =& $conn->query("select * from Users where guid = ? and deleted is null", array($guid));
			if ($row =& $result->fetchRow()) {
				$user = new User();
				$user->init($row);
				if ($setsession) {
					setcookie('session', self::makeCookie());
				}
			}
		}
		if (!$user) {
			$user = new User();
			if (!$guid) {
				$user->guid = str_replace('.', '', uniqid($_SERVER['REMOTE_ADDR'], true));
				setcookie('guid', $user->guid, time()+60*60*24*365*10);
			} else {
				$user->guid = $guid; 
			}
		}

		$conn->disconnect();
 		return $user;								
	}

	public static function loadUserByEmailPass($email, $pass) {
		$conn = WiscaDB::get();
		$result =& $conn->query("select * from Users where email = ? and encpass = ? and deleted is null", array($email, md5($pass)));
		$user = null;
		if ($row =& $result->fetchRow()) {
			$user = new User();
			$user->init($row);
			setcookie('guid', $user->guid);
			setcookie('session', self::makeCookie());
		}
		$conn->disconnect();
		return $user;					
	}

	public function emailTaken($email) {
		$conn = WiscaDB::get();
		$result = $conn->query("select * from Users where email = ?", array($email));
		$user = null;
		if ($row =& $result->fetchRow()) {
			$user = new User();
			$user->init($row);
		}
		$conn->disconnect();
		return $user;
	}

	public function save() {  //can't update the email address if it's taken
		$conn = WiscaDB::get();
		if ($this->newpassword) {
			$result = $conn->query("update Users set encpass = ? where guid = ?", array(md5($this->newpassword), $this->guid));
		}
		$deleted = ($this->deleted ? date("Y-m-d H:i:s") : null);
		$result = $conn->query("update Users set name = ?, email = ?, member = ?, admin = ?, deleted = ?, modified = NOW(), affiliation = ? where guid = ?", array($this->name, $this->email, $this->member, $this->admin, $deleted, $this->affiliation, $this->guid));
		$conn->disconnect();
	}

	public function make() {
		if ($user = $this->emailTaken($user->email)) {
			if ($user->deleted) {
				$user->deleted = false;
				$user->name = $this->name;
				$user->affiliation = $this->affiliation;
				$user->newpassword = $this->newpassword;
				$user->save();
				setcookie('guid', $user->guid);
			} else {
				return false;
			}
		} else {
			$conn = WiscaDB::get();
			$result = $conn->query("insert into Users (guid, created, modified, email, encpass, name, affiliation) values (?, NOW(), NOW(), ?, ?, ?, ?)", array($this->guid, $this->email, md5($this->newpassword), $this->name, $this->affiliation));  
			$conn->disconnect();
		}
		return true;
	}

	public function destroy() {
		setcookie('guid', '', time()-60*60*24);
		setcookie('session', '', time()-60*60*24);
	}

	private static function makeCookie() {
		$guid = $_COOKIE['guid']; 
		$ip = $_SERVER['REMOTE_ADDR']; 
		$useragent = $_SERVER['HTTP_USER_AGENT'];		
		return md5($guid.$ip.$useragent);
	}

	public function init($row) {
		$this->userId = $row[0];
		$this->name = $row[1];
		$this->email = $row[2];
		$this->admin = $row[4];
		$this->member = $row[5];
		$this->created = $row[6];
		$this->guid = $row[7];
		$this->modified = $row[8];
		$this->deleted = $row[9];
		$this->affiliation = $row[10];
	}

}
?>