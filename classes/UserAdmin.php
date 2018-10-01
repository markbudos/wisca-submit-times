<?php
class UserAdmin {

	public static function loadUsers($all = false) {
		$conn = WiscaDB::get();
		$result =& $conn->query("select * from Users".(!$all ? ' where deleted is null' : '').' order by name');
		$users = array();
		while ($row =& $result->fetchRow()) {
			$user = new User();
			$user->init($row);
			$users[] = $user;								
		}
		$conn->disconnect();
		return $users;
	}
}

?>