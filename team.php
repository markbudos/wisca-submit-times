<?php 
require_once 'classes/Session.php';

Session::getSession()->checkUser(Session::$ADMIN);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$school = School::lookupSchool($_REQUEST['schoolId']);
	$school->school = $_REQUEST['school'];
	$school->size = $_REQUEST['classification'];
	$school->girlscoaches['head'] = new Coach($_REQUEST['hc_girls'], null);
	$school->girlscoaches['assistant'] = new Coach($_REQUEST['ac_girls'], null);
	$school->girlscoaches['dive'] = new Coach($_REQUEST['dc_girls'], null);
	$school->boyscoaches['head'] = new Coach($_REQUEST['hc_boys'], null);
	$school->boyscoaches['assistant'] = new Coach($_REQUEST['ac_boys'], null);
	$school->boyscoaches['dive'] = new Coach($_REQUEST['dc_boys'], null);
	$school->save();
	if ($_REQUEST['school_assigned']) {
		$school->reassign($_REQUEST['school_assigned']);
		header("Location: team.php?teamId=".$_REQUEST['school_assigned']);
		die();
	}
}
if (!isset($_REQUEST['teamId'])) {
	header("Location: teams.php");
	die();
}

HeaderNav::stream("", array());

$school = School::lookupSchool($_REQUEST['teamId']);

echo '<h3>'.$school->school.'</h3>';

echo '<form action="" method="POST">';
echo '<div class="dataInput">Name: ';
echo '<input type="text" name="school" value="'.$school->school.'"/></div>';
echo '<input type="hidden" name="schoolId" value="'.$school->schoolId.'"/>';

echo '<div class="dataInput">Classification : ';
echo '<select name="classification" id="classification">';
echo '<option value="AAAA"'.($school->classification=='AAAA' ? ' selected' : '').'>AAAA</option>';
echo '<option value="AAA"'.($school->classification=='AAA' ? ' selected' : '').'>AAA</option>';
echo '<option value="AA"'.($school->classification=='AA' ? ' selected' : '').'>AA</option>';
echo '</select></div>';

$options = array(NULL, NULL, NULL, NULL, NULL, NULL);
$coaches = UserAdmin::loadUsers(false);

function getCoach($coach, $comp, $row) {
	$ret = '';
	if ($row === 0) {
		$ret .= '<option value="">-- unassigned --</option>';
	}
	return $ret.'<option '.($coach->userId == $comp->userId ? 'selected ' : '').'value="'.$coach->userId.'">'.$coach->name.', '.$coach->affiliation.'</option>';
}

$row = 0;
foreach ($coaches as $coach) {
	$options[0] .= getCoach($coach, $school->girlscoaches['head'], $row);
	$options[1] .= getCoach($coach, $school->girlscoaches['assistant'], $row);
	$options[2] .= getCoach($coach, $school->girlscoaches['dive'], $row);
	$options[3] .= getCoach($coach, $school->boyscoaches['head'], $row);
	$options[4] .= getCoach($coach, $school->boyscoaches['assistant'], $row);
	$options[5] .= getCoach($coach, $school->boyscoaches['dive'], $row);
	$row++;
}
echo '<div class="dataInput">';
echo '<h4>Girls Coaches</h4>';
echo '<span class="coach_type">head: </span><select name="hc_girls" id="hc_girls">'.$options[0].'</select><br/>'."\n";
echo '<span class="coach_type">assistant: </span><select name="ac_girls" id="ac_girls">'.$options[1].'</select><br/>'."\n";
echo '<span class="coach_type">dive: </span><select name="dc_girls" id="dc_girls">'.$options[2].'</select><br/>'."\n";
echo '</div>';
echo '<div class="dataInput">';
echo '<h4>Boys Coaches</h4>';
echo '<span class="coach_type">head: </span><select name="hc_boys" id="hc_boys">'.$options[3].'</select><br/>'."\n";
echo '<span class="coach_type">assistant: </span><select name="ac_boys" id="ac_boys">'.$options[4].'</select><br/>'."\n";
echo '<span class="coach_type">dive: </span><select name="dc_boys" id="dc_boys">'.$options[5].'</select><br/>'."\n";
echo '</div>';
echo '</br>';
echo '<div class="dataInput">Assign to : ';
echo '<select name="school_assigned" id="school_assigned">';
$schools = School::loadSchools(NULL);
echo '<option value=""></option>';
foreach ($schools as $s) {
	if ($s->schoolId != $_REQUEST['teamId']) {
		echo '<option value="'.$s->schoolId.'">'.$s->school.'</option>';
	}
}
echo '</select></div>';
echo '<input type="submit" value="Save" />';
echo '</form>';

$athletes = Athlete::loadAthletes($school->school);
echo '<ul>';
foreach ($athletes as $athlete) {
	echo '<li>'.$athlete->label().'</li>';
}
echo '</ul>';

?>

</body>