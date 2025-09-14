<?php
Session::getSession()->checkUser(Session::$REG);

$user = Session::getSession()->user;

$event = isset($_REQUEST['event']) ? $_REQUEST['event'] : '';
$classification = isset($_REQUEST['classification']) ? $_REQUEST['classification'] : '';
$classification = (!$classification && isset($_COOKIE['cl'])) ? $_COOKIE['cl'] : $classification;
$school= isset($_REQUEST['school']) ? $_REQUEST['school'] : '';
$points= isset($_REQUEST['points']) ? $_REQUEST['points'] : '';
$minutes= isset($_REQUEST['minutes']) ? $_REQUEST['minutes'] : '';
$seconds= isset($_REQUEST['seconds']) ? $_REQUEST['seconds'] : '';
$millis= isset($_REQUEST['millis']) ? $_REQUEST['millis'] : '';
$location= isset($_REQUEST['location']) ? $_REQUEST['location'] : '';
$athlete= isset($_REQUEST['athlete']) ? $_REQUEST['athlete'] : '';
$grade= isset($_REQUEST['grade']) ? $_REQUEST['grade'] : '';
$eventdate= isset($_REQUEST['date']) ? $_REQUEST['date'] : '';

$diving = false;
$relay = false;
if (strstr($event, 'Relay')) {
	$relay = true;
} else if (strstr($event, 'Diving')) {
	$diving = true;
}

$errors = array();
if ($_POST) {
	if (!$event) {
		$errors[] = "No event provided.";
	}
	if (!$classification) {
		$errors[] = "No classification set. (e.g. AAA)";
	}
	if (!$school) {
		$errors[] = "No school set.";
	}
	if (($millis == '' || $seconds == '') && !$diving) {
		$errors[] = "Must provide time for swimming events.";
	}
	if (!$points && $diving) {
		$errors[] = "Must provide points for diving.";
	}
	if (!$school) {
		$errors[] = "No school set.";
	}
	if (!$relay) {
		if (!$athlete) {
			$errors[] = "Must provide athlete for non-relay.";
		} else if (!preg_match("/\d+/", $athlete, $matches)) {
			$errors[] = "Must provide grade for new athletes. (e.g. Jane Smith (10) )";
		//} else if  (!$grade) {  //once grade is supplied, use this error check
		//	$errors[] = "Must provide grade for new athletes.";
		}
		if (!empty($matches) && ($matches[0] < 9 || $matches[0] > 12)) {
			$errors[] = "Grade must be 9-12.";
		}
		$athlete = preg_replace("/,/", "", $athlete);
	}
	if (!$location) {
		$errors[] = "Must provide location of result.";
	}
	if (!$eventdate) {
		$errors[] = "No date set.";
	}
	if (!$errors) {
		$results = array($points);
		if (!$diving) {
			$results = array($minutes, $seconds, $millis);
		}
		setcookie('cl', $classification, time() + (60 * 60 * 24 * 365 * 10));
		$result = Event::saveResult($school, $classification, $event, $athlete, 
			$results, $location, $eventdate, $errors);
		$ath = new Athlete();
		if (!$errors) {
			$ath->init($result);
                        $i = 0;
                        foreach ($result as $item) {
                                $result[$i++] = $item;     
                        }
                        $text = implode(",", $ath->formatResult($result, true));
			mail(
				'markbudos@gmail.com',
				'New submission',
				'A new submission has been generated: http://www.wiaca.alwaysdata.net/scripts/toptimes.php?c='.$classification."\n\n".$text
			);
			header("Location: mysubmissions.php?post=1");
		}
	}
}

$css = array("submit.css", 
		"yui/fonts-min.css",
		"yui/autocomplete.css",
		"yui/calendar.css");

$js = array("yui/yahoo-dom-event.js",
		"yui/connection-min.js",
		"yui/animation-min.js",
		"yui/datasource-min.js",
		"yui/autocomplete-min.js",
		"yui/calendar-min.js");

HeaderNav::stream("Submit Time", $css, $js);

echo '<h3>Submit Time</h3>';

if ($errors) {
	foreach ($errors as $error) {
		echo '<div class="errors">';
		echo '* '.$error.'<br>';
		echo '</div>';
	}
}
?>
<form method="post" action="submit.php">

<div id="eventAutoComplete" class="dataInput">
<label class="inputLabel" for="schoolInput">Event:&nbsp;</label>
<input name="event" id="eventInput" type="text" value="<?php  echo $event; ?>">
<div id="eventContainer"></div>
</div>

<div class="dataInput">
Classification:
<select name="classification" id="classification">
<?php
	echo '<option value="AAAA"'.($classification=='AAAA' ? ' selected' : '').'>AAAA</option>';
	echo '<option value="AAA"'.($classification=='AAA' ? ' selected' : '').'>AAA</option>';
	echo '<option value="AA"'.($classification=='AA' ? ' selected' : '').'>AA</option>';
?>
</select>
</div>

<div id="schoolAutoComplete" class="dataInput">
<label class="inputLabel" for="schoolInput">School:&nbsp;</label>
<input name="school" id="schoolInput" type="text" value="<?php  echo $school; ?>">
<div id="schoolContainer"></div>
</div>

<?php


echo '<div id="athleteAutoComplete"';
if (!$athlete || ($event && strstr($event, 'Relay'))) { echo ' style="display:none;"'; }
echo 'class="dataInput">';
echo '<label for="athleteInput">Athlete:&nbsp;</label>';
echo '<input name="athlete" id="athleteInput" type="text" value="'.$athlete.'">';
echo '<div id="athleteContainer"></div>';
echo '</div>';

echo '<div class="dataInput" id="gradeInput" style="display:none;">Grade: ';
echo '<select name="grade" id="grade"><option value="12">12</option><option value="11">11</option><option value="10">10</option><option value="9">9</option></select>';
echo '</div>';
?>

<?php
echo '<div class="dataInput" id="timeSelector"';
if ($diving) { echo ' style="display:none;"'; }
echo '>';
echo 'Time:&nbsp;<input id="minutes" style="width:3em;" type="text" name="minutes" value="'.$minutes.'"> : ';
echo '<input id="seconds" type="text" style="width:3em;" name="seconds" value="'.$seconds.'"> . ';
echo '<input id="millis" type="text" style="width:3em;" name="millis" value="'.$millis.'"></div>';
echo '<div class="dataInput" id="pointSelector"';
if (!$diving) { echo ' style="display:none;"'; }
echo '>';
echo 'Points:&nbsp;<input name="points" id="points" style="width:3em;" type="text" value="'.$points.'"></div>';
?>

<div id="locationAutoComplete" class="dataInput">
<label for="locationInput">Location:&nbsp;</label>
<input name="location" id="locationInput" type="text" value="<?php  echo $location; ?>">
<div id="locationContainer"></div>
</div>

<div id="calContainer" class="dataInput"></div><br clear="all">
<div style="display:none">
<input type="text" name="date" id="eventdate" value="<?php  echo $eventdate; ?>"/> 
</div>

<script type="text/javascript" src="autocomplete.js">
</script>

<input type="submit" class="dataInput" value="Submit">
</form>

</body>
</html>