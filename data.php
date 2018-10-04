<?php
require_once 'classes/Session.php';

if (!isset($_REQUEST['api'])) {
	return;
}
$api = $_REQUEST['api'];

$ret = array();
switch ($api) {
	case 'event':
		$ret = Event::loadEvents();
		break;
	case 'school':
		$ret = School::loadSchools($_REQUEST['classification']);
		break;
	case 'athlete':
		$ret = Athlete::loadAthletes($_REQUEST['school']);
		break;
	case 'location':
		$ret = Event::loadLocations();
		break;
}


foreach ($ret as $item) {
	if (strstr($item->label(), $_REQUEST['query'])) {
		echo $item->label()."\n";
	}
}

?>