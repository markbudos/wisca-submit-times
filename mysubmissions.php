<?php 
require_once 'classes/Session.php';

Session::getSession()->checkUser(Session::$REG);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

}
HeaderNav::stream("Submissions");

echo '<h3>Submissions by '.Session::getSession()->user->name.'</h3>';
//set up the year and yearend variables...girls and boys will be based on time period of the season..
$month = date('n');
//default to girls/boys based on month
$gender = isset($_REQUEST['g']) ? $_REQUEST['g'] : ($month > 10 || $month < 6 ? 'm' : 'f'); 
$year = isset($_REQUEST['y']) ? $_REQUEST['y'] : date('Y');
$endyear = $year;
if ($gender == 'm') {
	if ($month < 10) {
		$year--;
	} else {
		$endyear++;
	}
}

$startdate = $year.($gender == 'f' ? '/08/01' : '/11/01');
$enddate = $endyear.($gender == 'f' ? '/11/01' : '/06/01');

$results = Event::loadResults($startdate, $enddate, null, Session::getSession()->user->userId);
//$results = Event::loadResults($startdate, $enddate, null, 19);

if (isset($_REQUEST['post'])) {
	echo '<div class="errors">All new times (and non-bold times) must be verified.  Please forward a scan of the card or a Meet Manager result to <a href="mailto:jefflowell9969@gmail.com?subject=Results for approval.">jefflowell9969@gmail.com</a>.<br></div>';
}

echo '<table>';  
$eventMap = array();
$lastDate = null;
foreach ($results as $result) {
	$athlete = new Athlete(); //dumb...sometimes we don't even have an athlete.
	$athlete->init($result);
	if ($lastDate != $result['date']) {
		$lastDate = $result['date']; //separate new dates with a newline
		echo '<tr><td colspan="5"></tr>';
	}
	$key = $result['eventId'].'-'.$result['type'].'-'.$result['participantId'];
	$dupe = false;
	if (isset($eventMap[$key])) { $dupe = true; } else { $eventMap[$key] = 1; }
	$row = $athlete->formatResult($result, true);
	echo '<tr>';  
	$i = 0;
	$widths = array(0, 90, 300, 150, 60, 150, 200);
	$style = null;
	if ($result['validated']) { $style .= 'font-weight:bold;'; }
	foreach ($row as $td) {
		if (($i++ <= 3) && $style) {
			echo '<td width="'.$widths[$i].'" style="'.$style.'">'.$td.'</td>';
		} else {
			echo '<td width="'.$widths[$i].'">'.$td.'</td>';
		}
	}
	echo '</tr>';

}
echo '</table>';  

?>

</body>