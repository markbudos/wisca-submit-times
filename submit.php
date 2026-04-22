<?php
ob_start();
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
				'A new submission has been generated: http://www.wisca.org/scripts/toptimes.php?c='.$classification."\n\n".$text
			);
			header("Location: mysubmissions.php?post=1");
		}
	}
}

HeaderNav::stream("Submit Time");

// Pre-load data for datalists
$allEvents    = Event::loadEvents();
$allSchools   = School::loadSchools($classification ?: 'AAA');
$allLocations = Event::loadLocations();

echo '<h2>Submit Time</h2>';

if ($errors) {
	echo '<div class="alert alert-error">';
	foreach ($errors as $error) {
		echo '<div>• '.$error.'</div>';
	}
	echo '</div>';
}
?>

<?php
// ── Datalists ─────────────────────────────────────────────────
echo '<datalist id="dl-events">';
foreach ($allEvents as $e) { echo '<option value="'.htmlspecialchars($e->label()).'">'; }
echo '</datalist>';

echo '<datalist id="dl-schools">';
foreach ($allSchools as $s) { echo '<option value="'.htmlspecialchars($s->label()).'">'; }
echo '</datalist>';

echo '<datalist id="dl-locations">';
foreach ($allLocations as $l) { echo '<option value="'.htmlspecialchars($l->label()).'">'; }
echo '</datalist>';

echo '<datalist id="dl-athletes"></datalist>'; // populated by JS when school changes
?>

<form method="post" action="submit.php" class="submit-form">

  <div class="field">
    <label for="eventInput">Event</label>
    <input name="event" id="eventInput" type="text" data-list="dl-events"
           value="<?php echo htmlspecialchars($event); ?>" autocomplete="off">
  </div>

  <div class="field">
    <label for="classification">Classification</label>
    <select name="classification" id="classification">
      <?php
        echo '<option value="AAAA"'.($classification=='AAAA' ? ' selected' : '').'>AAAA</option>';
        echo '<option value="AAA"'.($classification=='AAA' ? ' selected' : '').'>AAA</option>';
        echo '<option value="AA"'.($classification=='AA' ? ' selected' : '').'>AA</option>';
      ?>
    </select>
  </div>

  <div class="field">
    <label for="schoolInput">School</label>
    <input name="school" id="schoolInput" type="text" data-list="dl-schools"
           value="<?php echo htmlspecialchars($school); ?>" autocomplete="off">
  </div>

  <?php
  $hideAthlete = (!$athlete || ($event && strstr($event, 'Relay'))) ? ' style="display:none;"' : '';
  echo '<div id="athleteAutoComplete" class="field"'.$hideAthlete.'>';
  echo '<label for="athleteInput">Athlete</label>';
  echo '<div>';
  echo '<input name="athlete" id="athleteInput" type="text" data-list="dl-athletes" value="'.htmlspecialchars($athlete).'" autocomplete="off">';
  echo '<span class="field-hint">Include grade, e.g. Jane Smith (10)</span>';
  echo '</div></div>';
  ?>

  <?php
  $hideTime   = $diving ? ' style="display:none;"' : '';
  $hidePoints = !$diving ? ' style="display:none;"' : '';
  echo '<div class="field" id="timeSelector"'.$hideTime.'>';
  echo '<label>Time</label>';
  echo '<div class="time-inputs">';
  echo '<input id="minutes" class="time-part" type="text" name="minutes" value="'.htmlspecialchars($minutes).'" placeholder="min">';
  echo '<span class="time-sep">:</span>';
  echo '<input id="seconds" class="time-part" type="text" name="seconds" value="'.htmlspecialchars($seconds).'" placeholder="sec">';
  echo '<span class="time-sep">.</span>';
  echo '<input id="millis" class="time-part" type="text" name="millis" value="'.htmlspecialchars($millis).'" placeholder="100s">';
  echo '</div></div>';
  echo '<div class="field" id="pointSelector"'.$hidePoints.'>';
  echo '<label for="points">Points</label>';
  echo '<input name="points" id="points" class="time-part" type="text" value="'.htmlspecialchars($points).'">';
  echo '</div>';
  ?>

  <div class="field">
    <label for="locationInput">Location</label>
    <input name="location" id="locationInput" type="text" data-list="dl-locations"
           value="<?php echo htmlspecialchars($location); ?>" autocomplete="off">
  </div>

  <div class="field">
    <label for="eventdate">Date</label>
    <input type="date" name="date" id="eventdate" class="date-input"
           value="<?php echo $eventdate ? date('Y-m-d', strtotime($eventdate)) : ''; ?>">
  </div>

  <div class="field">
    <button type="submit" class="btn">Submit Time</button>
  </div>

</form>

<script src="autocomplete.js"></script>