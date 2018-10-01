<?php 

Session::getSession()->checkUser(Session::$MEMBER);
$classification = isset($_REQUEST['c']) ? $_REQUEST['c'] : (isset($_COOKIE['cl']) ? $_COOKIE['cl'] : 'AAAA');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && Session::getSession()->user->admin) {
//process potential approvals and denials
	foreach ($_POST as $var=>$post) {
		$keyval = explode("_", $var);
		if (count($keyval) > 1 && $keyval[0] === 'result') {
			if ($post === 'deny') {
				Event::setResult($keyval[1], -1);
			} else if ($post === 'accept') {
				Event::setResult($keyval[1], 1);
			}
		}
	}
}
HeaderNav::stream("View Times");
?>

<h3>State Times <?php echo $classification; ?></h3>

<?php 
//set up the year and yearend variables...girls and boys will be based on time period of the season..
$month = date('n');
$day = date('d');
//default to girls/boys based on month
$gender = isset($_REQUEST['g']) ? $_REQUEST['g'] : (($month == 12 || ($month == 11 && $day > 10)) || $month < 6 ? 'm' : 'f'); 
$year = isset($_REQUEST['y']) ? $_REQUEST['y'] : date('Y');
$endyear = $year;
if ($gender == 'm') {
	if ($month < 10) {
		$year--;
	} else {
		$endyear++;
	}
}

$tmp = array(0,0,0,0,0,0,0,0);
$tmp[(strlen($classification) - 2) + (4 * ($gender == 'f' ? 0 : 1))] = 1;

function genLink($class, $url, $wrap) {
	$ret = '';
	if ($wrap) { $ret .= '<a href="'.$url.'&c='.$class.'">'; }
	$ret .= ($class === 'AAAAA' ? 'All' : $class);
	if ($wrap) { $ret .= '</a>'; }
	return $ret;
}

echo 'Girls: '.genLink('AA', 'toptimes.php?g=f', !$tmp[0]).' | ';
echo genLink('AAA', 'toptimes.php?g=f', !$tmp[1]).' | ';
echo genLink('AAAA', 'toptimes.php?g=f', !$tmp[2]).' | ';
echo genLink('AAAAA', 'toptimes.php?g=f', !$tmp[3]).' &nbsp;&nbsp;&nbsp;';
echo 'Boys: '.genLink('AA', 'toptimes.php?g=m', !$tmp[4]).' | ';
echo genLink('AAA', 'toptimes.php?g=m', !$tmp[5]).' | ';
echo genLink('AAAA', 'toptimes.php?g=m', !$tmp[6]).' | ';
echo genLink('AAAAA', 'toptimes.php?g=m', !$tmp[7]).' &nbsp;&nbsp;&nbsp;';

$startdate = '';
$enddate = '';
if ($gender == 'm') {
	$startdate = $year.'/11/10';
	$enddate = $endyear.'/06/01';
} else {
	$startdate = $year.'/08/01';
	$enddate = $endyear.'/11/10';
}

$events = Event::loadEvents();

$results = Event::loadResults($startdate, $enddate, $classification == 'AAAAA' ? NULL : $classification);
foreach ($results as $result) {
	$events[$result['eventId']]->results[] = $result;
}

echo '<form method="post" action="toptimes.php">';
//pass through state onto following pages while adminning...
echo '<input type="hidden" name="c" value="'.$classification.'">';
echo '<input type="hidden" name="y" value="'.$endyear.'">';
echo '<input type="hidden" name="g" value="'.$gender.'">';

$eventMap = array();

foreach ($events as $event) {
	echo '<h4>'.$event->label().'</h4>';
	echo '<table>';
	foreach ($event->results as $result) {
		$athlete = new Athlete(); //dumb...sometimes we don't even have an athlete.
		$athlete->init($result);
		$key = $result['eventId'].'-'.$result['type'].'-'.$result['participantId'];

		$dupe = FALSE;
		if (isset($eventMap[$key])) {
			$dupe = TRUE;
		} else {
			$eventMap[$key] = 1;
		}
		$style = '';
		if ($dupe) { $style .= 'font-style:italic;'; }
		if ($result['validated']) { $style .= 'font-weight:bold;'; }
		
		$row = $athlete->formatResult($result, false);
		echo '<tr>';  
		if (Session::getSession()->user->admin) {
			echo '<td style="width:160px;"><input type="radio" name="result_'.$result['resultId'].'" value="accept"'.($result['validated'] ? ' disabled' : '').'> Accept';
			echo '<input type="radio" name="result_'.$result['resultId'].'" value="deny"> Delete</td>';
		}
		$i = 0;
		$widths = array(100, 80, 220, 300, 60, 300);
		foreach ($row as $td) {
			if (($i++ == 0 || $i == 3 || $i == 4) && $style) {
				echo '<td width="'.$widths[$i].'" style="'.$style.'">'.$td.'</td>';
			} else {
				echo '<td width="'.$widths[$i].'">'.$td.'</td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
}

if (Session::getSession()->user->admin) {
	echo '<div style="margin-top: 2em"><input type="submit" value="Apply" /></div>';
}
?>
</body>