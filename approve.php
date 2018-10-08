<?php
require_once 'classes/Session.php';

Session::getSession()->checkUser(Session::$ADMIN);

if (isset($_REQUEST['resultid']) && isset($_REQUEST['action'])) {
    $resultId = $_REQUEST['resultid'];
    $action = $_REQUEST['action'];
    if ($action == 'accept') {
        Event::setResult($resultId, 1);
    } else if ($action == 'suspend') {
        Event::setResult($resultId, 0);
    } else if ($action == 'delete') {
        Event::setResult($resultId, -1);
    }
}
header('Content-Type: application/json');
echo json_encode( array("resultId"=>$resultId, "action"=>$action) );

?>
