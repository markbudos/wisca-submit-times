<?php

if (!isset($_REQUEST['api'])) {
	return;
}
$api = $_REQUEST['api'];
$query = isset($_REQUEST['query']) ? strtolower($_REQUEST['query']) : '';

$ret = array();
switch ($api) {
	case 'event':
		$ret = Event::loadEvents();
		break;
	case 'school':
		$ret = School::loadSchools($_REQUEST['classification'] ?? '');
		break;
	case 'athlete':
		$ret = Athlete::loadAthletes($_REQUEST['school'] ?? '');
		break;
	case 'location':
		$ret = Event::loadLocations();
		break;
}

$matches = array();
foreach ($ret as $item) {
	if (!$query || strstr(strtolower($item->label()), $query)) {
		$matches[] = $item->label();
	}
}

header('Content-Type: application/json');
echo json_encode($matches);