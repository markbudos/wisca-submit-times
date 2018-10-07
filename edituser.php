<?php
require_once 'classes/Session.php';

Session::getSession()->checkUser(Session::$ADMIN);
$user = null;

if (isset($_REQUEST['userid'])) {
    $userId = $_REQUEST['userid'];
    $user = User::loadUserByUserId($_REQUEST['userid']);
    if (isset($_REQUEST['admin'])) {
        $user->admin = $_REQUEST['admin'];
    }
    if (isset($_REQUEST['member'])) {
        $user->member = $_REQUEST['member'];
    }
    if (isset($_REQUEST['deleted'])) {
        $user->deleted = $_REQUEST['deleted'];
    }
    $user->save();
    $user = User::loadUserByUserId($user->userId);
}

header('Content-Type: application/json');
echo json_encode($user);

?>