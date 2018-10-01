<?php 
Session::getSession()->checkUser(Session::$WWW);
$user = Session::getSession()->user; 
?>

<?php
$rd = 'account.php';
if (isset($_REQUEST['rd'])) {
	$rd = $_REQUEST['rd'];
}

if ($user->email) {
	//already registered...
	HeaderNav::stream("Account");
	echo '<h3 style="text-align: left; margin-bottom: 1em">Edit Account:</h3>';
	if (isset($_GET['msg'])) {	echo '<h4>'.$_GET['msg'].'</h4>'; }
	echo '<form method="post" action="reg.php">';
	if (!$user->member && !$user->admin) {
		echo '<p>As a registered user, you can submit times.  Please contact <a href="mailto:newportswimdive@live.com?subject=Membership information.">WISCA Membership</a> for membership information and the ability to view all submissions.</p>';
	}
	echo '<input type="hidden" name="type" value="save" />';
	echo '<input type="hidden" name="rd" value="'.$rd.'" />';
	echo '<div style="margin-bottom: 9px">E-Mail : ';
	echo '<input type="text" name="newemail" value="'.$user->email.'" /></div>';
	echo '<div style="margin-bottom: 9px">New Password: ';
	echo '<input type="password" name="newpassword"/> (leave blank to keep existing password)</div>';
	echo '<div style="margin-bottom: 9px">Name: ';
	echo '<input type="text" name="name" value="'.$user->name.'"/></div>';
	echo '<div style="margin-bottom: 9px">Affiliation: ';
	echo '<input type="text" name="aff" value="'.$user->affiliation.'"/></div>';
	echo '<div style="margin-bottom: 9px">';
	echo '<input type="submit" value="Save" /></div></form>';
} else if (isset($_GET['register']) && $_GET['register'] == '1') {
	HeaderNav::stream("Account");
	echo '<h3 style="text-align: left; margin-bottom: 1em">New Account:</h3>';
	if (isset($_GET['msg'])) {
		echo '<h4>'.$_GET['msg'].'</h4>';
	}
	echo '<form method="post" action="reg.php">';
	echo '<input type="hidden" name="type" value="new" />';
	echo '<input type="hidden" name="rd" value="'.$rd.'" />';
	echo '<div style="margin-bottom: 9px">E-Mail : ';
	echo '<input type="text" name="email" value="" /></div>';
	echo '<div style="margin-bottom: 9px">Password: ';
	echo '<input type="password" name="password"/></div>';
	echo '<div style="margin-bottom: 9px">Name: ';
	echo '<input type="text" name="name"/></div>';
	echo '<div style="margin-bottom: 9px">Affiliation: ';
	echo '<input type="text" name="aff" value="'.$user->affiliation.'"/></div>';
	echo '<div style="margin-bottom: 9px">';
	echo '<input type="submit" value="Register" /></div></form>';
} else {
	HeaderNav::stream("Log in");
	echo '<h3 style="text-align: left; margin-bottom: 1em">Log in:</h3>';
	if (isset($_GET['msg'])) {
		echo '<h4>'.htmlentities($_GET['msg']).'</h4>';
	}
	echo '<form method="post" action="reg.php">';
	echo '<input type="hidden" name="rd" value="'.$rd.'" />';
	echo '<div style="margin-bottom: 9px">E-Mail : ';
	echo '<input type="text" name="email" value="" /></div>';
	echo '<div style="margin-bottom: 9px">Password: ';
	echo '<input type="password" name="password"/></div>';
	echo '<p>Please contact <a href="mailto:newportswimdive@live.com?subject=Membership information.">WISCA Membership</a> if you have forgotten your password.</p>';
	echo '<div style="margin-bottom: 9px">';
	echo '<input type="submit" value="Log In" /></div></form>';
}

echo '</form>';

?>

</body>
</html>

