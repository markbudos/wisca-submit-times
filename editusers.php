<?php 
Session::getSession()->checkUser(Session::$MEMBER);
HeaderNav::stream("Edit Users");
$thisuser = Session::getSession()->user;

$all = false;
if ($thisuser->admin) {
	echo '<h3>Edit Users</h3>';
	$all = isset($_REQUEST['all']);
	echo $all ? '<a href="editusers.php">Hide deleted</a>' : '<a href="editusers.php?all">Show all</a>';
	echo '<form method="post" action="editusers.php">';
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		foreach ($_POST as $key=>$value) {
			$keys = explode('_', $key);
			if ($keys[0] == 'member' && $_POST['h'.$key] == "0") {
				$user = User::loadUserByUserId($keys[1]);
				$user->member = 1;
				$user->save();
				echo $user->name.' is now a member.<br>';
			} else if ($keys[0] == 'admin' && $_POST['h'.$key] == '0') {
				$user = User:: loadUserByUserId($keys[1]);
				$user->admin = 1;
				$user->save();
				echo $user->name.' is now an admin.<br>';
			} else if ($keys[0] == 'hmember' && $value == '1' && !isset($_POST['member_'.$keys[1]])) {
				$user = User:: loadUserByUserId($keys[1]);
				$user->member = 0;
				$user->save();
				echo $user->name.' is no longer a member.<br>';
			} else if ($keys[0] == 'hadmin' && $value == '1' && !isset($_POST['admin_'.$keys[1]])) {
				$user = User:: loadUserByUserId($keys[1]);
				$user->admin = 0;
				$user->save();
				echo $user->name.' is no longer an admin.<br>';
			} else if ($keys[0] == 'deleted' && $_POST['h'.$key] == "0") {
				$user = User:: loadUserByUserId($keys[1]);
				$user->deleted = true;
				$user->save();
				echo $user->name.' has been deleted.<br>';
			} else if ($keys[0] == 'hdeleted' && $value == '1' && !isset($_POST['deleted_'.$keys[1]])) {
				$user = User:: loadUserByUserId($keys[1]);
				$user->deleted = false;
				$user->save();
				echo $user->name.' has been restored.<br>';
			}
		}
	}
} else {
	echo '<h3>Users</h3>';
}
echo '<table>';

$users = UserAdmin::loadUsers($all);

if ($thisuser->admin) {
	echo '<tr><th>Name</th><th>Email</th><th>Member</th><th>Admin</th><th>Affiliation</th><th>Created</th><th>Modified</th><th>Deleted</th></tr>';
} else {
	echo '<tr><th>Name</th><th>Email</th><th>Member</th><th>Affiliation</th><th>Created</th></tr>';
}
foreach ($users as $user) {
	echo '<tr>';
	echo '<td width="200">'.$user->name.($user->deleted ? ' ('.$user->deleted.') ' : '').'</td>';
	echo '<td>'.$user->email.'</td>';
	if ($thisuser->admin) {
		echo '<td align="center"><input name="member_'.$user->userId.'" type="checkbox" '.($user->member ? ' checked="checked"' : '').'><input name="hmember_'.$user->userId.'" type="hidden" value="'.$user->member.'"</td>';
		echo '<td align="center"><input name="admin_'.$user->userId.'" type="checkbox" '.($user->admin ? ' checked="checked"' : '').'><input name="hadmin_'.$user->userId.'" type="hidden" value="'.$user->admin.'"</td>';
	} else {
		echo '<td align="center">'.($user->member ? "Yes" : "No").'</td>';
	}
	echo '<td width="200">'.$user->affiliation.'</td>';
	echo '<td width="170">'.$user->created.'</td>';
	if ($thisuser->admin) {
		echo '<td width="170">'.$user->modified.'</td>';
		echo '<td align="center"><input name="deleted_'.$user->userId.'" '.($user->deleted ? 'checked="on"' : '').' type="checkbox"><input name="hdeleted_'.$user->userId.'" type="hidden" value="'.($user->deleted ? "1" : "0").'"></td>';
	}
	echo '</tr>';
}

echo '</table>';
if ($thisuser->admin) {
	echo '<div style="margin-bottom: 9px"><input type="submit" value="Apply" /></div></form>';
}
?>
</body>