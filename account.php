<?php 
require_once 'classes/Session.php';

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
	echo '<h2>Edit Account</h2>';
	if (isset($_GET['msg'])) { echo '<div class="alert alert-info">'.$_GET['msg'].'</div>'; }
	if (!$user->member && !$user->admin) {
		echo '<p>As a registered user, you can submit times. Please contact <a href="mailto:nooksack_swimmer@hotmail.com?subject=Membership information.">WISCA Membership</a> for membership information and the ability to view all submissions.</p>';
	}
	echo '<form method="post" action="reg.php">';
	echo '<input type="hidden" name="type" value="save" />';
	echo '<input type="hidden" name="rd" value="'.$rd.'" />';
	echo '<div class="field"><label>E-Mail</label><input type="text" name="newemail" value="'.$user->email.'" /></div>';
	echo '<div class="field"><label>New Password</label><input type="password" name="newpassword" placeholder="Leave blank to keep existing" /></div>';
	echo '<div class="field"><label>Name</label><input type="text" name="name" value="'.$user->name.'" /></div>';
	echo '<div class="field"><label>Affiliation</label><input type="text" name="aff" value="'.$user->affiliation.'" /></div>';
	echo '<button type="submit" class="btn">Save</button>';
	echo '</form>';
} else if (isset($_GET['register']) && $_GET['register'] == '1') {
	HeaderNav::stream("Account");
	echo '<h2>Create Account</h2>';
	if (isset($_GET['msg'])) { echo '<div class="alert alert-info">'.$_GET['msg'].'</div>'; }
	echo '<form method="post" action="reg.php">';
	echo '<input type="hidden" name="type" value="new" />';
	echo '<input type="hidden" name="rd" value="'.$rd.'" />';
	echo '<div class="field"><label>E-Mail</label><input type="text" name="email" /></div>';
	echo '<div class="field"><label>Password</label><input type="password" name="password" /></div>';
	echo '<div class="field"><label>Name</label><input type="text" name="name" /></div>';
	echo '<div class="field"><label>Affiliation</label><input type="text" name="aff" value="'.$user->affiliation.'" /></div>';
	echo '<button type="submit" class="btn">Register</button>';
	echo '</form>';
} else {
	HeaderNav::stream("Log in");
	echo '<h2>Log In</h2>';
	if (isset($_GET['msg'])) { echo '<div class="alert alert-error">'.htmlentities($_GET['msg']).'</div>'; }
	echo '<form method="post" action="reg.php">';
	echo '<input type="hidden" name="rd" value="'.$rd.'" />';
	echo '<div class="field"><label>E-Mail</label><input type="text" name="email" /></div>';
	echo '<div class="field"><label>Password</label><input type="password" name="password" /></div>';
	echo '<p style="margin-bottom:1rem">Forgot your password? Contact <a href="mailto:nooksack_swimmer@hotmail.com?subject=Membership information.">WISCA Membership</a>.</p>';
	echo '<button type="submit" class="btn">Log In</button>';
	echo '</form>';
}

?>
</div></main>
</body>
</html>

