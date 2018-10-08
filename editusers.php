<?php 
require_once 'classes/Session.php';

Session::getSession()->checkUser(Session::$MEMBER);
HeaderNav::stream("Edit Users");
$thisuser = Session::getSession()->user;

$all = false;
if ($thisuser->admin) {
	echo '<h3>Edit Users</h3>';
	$all = isset($_REQUEST['all']);
	echo $all ? '<a href="editusers.php">Hide deleted</a>' : '<a href="editusers.php?all">Show all</a>';
} else {
	echo '<h3>Users</h3>';
}
echo '<form method="post" action="editusers.php">';
echo '<table>';

$users = UserAdmin::loadUsers($all);

if ($thisuser->admin) {
	echo '<tr><th>Name</th><th>Email</th><th>Member</th><th>Admin</th><th>Deleted</th><th>Affiliation</th><th>Created</th><th>Modified</th></tr>';
} else {
	echo '<tr><th>Name</th><th>Email</th><th>Member</th><th>Affiliation</th><th>Created</th></tr>';
}
foreach ($users as $user) {
	echo '<tr onclick="userCheck(this)">';
	echo '<td width="200">'.$user->name.($user->deleted ? ' ('.$user->deleted.') ' : '').'</td>';
	echo '<td>'.$user->email.'</td>';
	if ($thisuser->admin) {
		echo '<td align="center"><input name="member_'.$user->userId.'" type="checkbox" '.($user->member ? ' checked="checked"' : '').'><input type="hidden" value="'.$user->member.'"></td>';
		echo '<td align="center"><input name="admin_'.$user->userId.'" type="checkbox" '.($user->admin ? ' checked="checked"' : '').'><input type="hidden" value="'.$user->admin.'"></td>';
		echo '<td align="center"><input name="deleted_'.$user->userId.'" type="checkbox" '.($user->deleted ? 'checked="checked"' : '').'><input type="hidden" value="'.($user->deleted ? "1" : "0").'"></td>';
	} else {
		echo '<td align="center">'.($user->member ? "Yes" : "No").'</td>';
	}
	echo '<td width="200">'.$user->affiliation.'</td>';
	echo '<td width="170">'.$user->created.'</td>';
	if ($thisuser->admin) {
		echo '<td width="170">'.$user->modified.'</td>';
	}
	echo '</tr>';
}

echo '</table></form>';
?>

<script>
function userCheck(row) {
	var member = row.children[2].children;
	var admin = row.children[3].children;
	var del = row.children[4].children;
	var qs = "";
	if (member[0].checked != member[1].value) {
		member[1].value = member[0].checked ? 1 : 0;
		qs += "&member="+member[1].value;
	}
	if (admin[0].checked != admin[1].value) {
		admin[1].value = admin[0].checked ? 1 : 0;
		qs += "&admin="+admin[1].value;
	}
	if (del[0].checked != del[1].value) {
		del[1].value = (del[0].checked ? 1 : 0);
		qs += "&deleted="+del[1].value;
		if (del[1].value == 1) {
			setTimeout(function(tohide) {	
				tohide.style.display = 'none';
			}, 500, row);
		}
	}
	if (qs) {
 		var userid = member[0].name.split('_')[1];
		Wisca.ajax("/scripts/edituser.php?userid="+userid+qs, function(responseText) {
			var response = responseText;
		});
	}
}
</script>

<script type="text/javascript" src="wisca.js">
</script>
</body>
</html>