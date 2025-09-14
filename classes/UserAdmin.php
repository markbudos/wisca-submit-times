<?php
class UserAdmin {

	public static function loadUsers($all = false) {
		$conn = WiscaDB::get();

                $sql = "select * from Users order by name";
                if (!$all) {
                        $sql = "select * from Users where deleted is null order by name";
                }
		$stmt = $conn->prepare($sql);
                $stmt->execute();
		$users = array();
		while ($row = $stmt->fetch()) {
			$user = new User();
			$user->init($row);
			$users[] = $user;								
		}
		$conn = null;
		return $users;
	}

}

?>