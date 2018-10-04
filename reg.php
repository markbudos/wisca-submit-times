<?php 
require_once 'classes/Session.php';

$user = Session::getSession()->user;

$msg = NULL;

//if password is wrong, then user will be empty
if ($user && isset($_REQUEST['type']) && $_REQUEST['type'] == 'save' && $_REQUEST['newemail']) {  
	$user->newpassword = $_REQUEST['newpassword'] ? $_REQUEST['newpassword'] : null;
	$user->email = $_REQUEST['newemail'] ? $_REQUEST['newemail'] : $user->email;
	$user->name = $_REQUEST['name'];
	$user->affiliation = $_REQUEST['aff'];
	$user->save();
	$msg = 'Account successfully saved.';
	Session::getSession()->reset();
} else if (($user && !$user->email) && isset($_REQUEST['type']) && $_REQUEST['type'] == 'new'  && isset($_REQUEST['email']) && ($_REQUEST['password'])) {
	$user->email = $_REQUEST['email'];
	$user->name = $_REQUEST['name'];
	$user->affiliation = $_REQUEST['aff'];
	$user->newpassword = $_REQUEST['password'];
	if ($user->make()) {
		$msg = 'Account successfully created.';
		Session::getSession()->reset();
	} else {
		$msg = 'Email address already registered.';
	}
} else if (isset($_REQUEST['logout'])) {
	$msg = 'Successful logout.';
	$user->destroy();
} else if (isset($_REQUEST['email'])) {
	$msg = 'Successful login.';
}

if (isset($_REQUEST['rd'])) {
	$rd = $_REQUEST['rd'];
	if (!$user) {
		$msg = 'Bad Login';
	}
	$rd .= '?msg='.urlencode($msg);
	header('Location: '.$rd);
}

?>