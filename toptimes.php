<?php 
require_once 'classes/Session.php';

Session::getSession()->checkUser(Session::$MEMBER);
$classification = isset($_REQUEST['c']) ? $_REQUEST['c'] : (isset($_COOKIE['cl']) ? $_COOKIE['cl'] : 'AAAA');

HeaderNav::stream("View Times", null, null, 'wide');

echo '<h3>State Times '.$classification.'</h3>';

//set up the year and yearend variables...girls and boys will be based on time period of the season..
$month = date('n');
$day = date('d');
//default to girls/boys based on month
$gender = isset($_REQUEST['g']) ? $_REQUEST['g'] : (($month == 12 || ($month == 11 && $day > 10)) || $month < 6 ? 'm' : 'f'); 
$year = isset($_REQUEST['y']) ? $_REQUEST['y'] : date('Y');
$endyear = $year;
if ($gender == 'm') {
	if ($month < 11) {
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
	echo '<div class="table-scroll"><table>';
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
			$vid = $result['validated'] ? '1' : '0';
			$alabel = $result['validated'] ? 'Suspend' : 'Accept';
			echo '<td class="col-actions">';
			echo '<button type="button" class="btn-xs btn-accept" data-id="'.$result['resultId'].'" data-validated="'.$vid.'" onclick="resultAccept(this)">'.$alabel.'</button>';
			echo '<button type="button" class="btn-xs btn-delete" data-id="'.$result['resultId'].'" onclick="resultDelete(this)">Del</button>';
			echo '</td>';
		}
		$i = 0;
		$widths = array(100, 80, 280, 60, 220, 300);
		foreach ($row as $td) {
			$nameClass = ($i === 0) ? ' class="col-name"' : '';
			if (($i++ == 0 || $i == 2 || $i == 3) && $style) {
				echo '<td'.$nameClass.' width="'.$widths[$i].'" style="'.$style.'">'.$td.'</td>';
			} else {
				echo '<td'.$nameClass.' width="'.$widths[$i].'">'.$td.'</td>';
			}
		}
		echo '</tr>';
	}
	echo '</table></div>';
	echo '</form>';
}

?>
<script>
function resultDelete(btn) {
	var tr = btn.closest('tr');
	Wisca.ajax("/scripts/approve.php?action=delete&resultid="+btn.dataset.id, function() {});
	setTimeout(function() { tr.style.display = 'none'; }, 800);
}

function resultAccept(btn) {
	var tr = btn.closest('tr');
	var validated = btn.dataset.validated === '1';
	var action = validated ? 'suspend' : 'accept';
	Wisca.ajax("/scripts/approve.php?action="+action+"&resultid="+btn.dataset.id, function() {});
	setTimeout(function() {
		var nowValidated = !validated;
		btn.dataset.validated = nowValidated ? '1' : '0';
		btn.textContent = nowValidated ? 'Suspend' : 'Accept';
		tr.style.fontWeight = nowValidated ? 'bold' : '';
	}, 800);
}
</script>

<script type="text/javascript" src="wisca.js"></script>
</body>
