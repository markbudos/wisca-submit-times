<?php 
Session::getSession()->checkUser(Session::$REG);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

}
HeaderNav::stream("Teams");

echo '<h3>Teams</h3>';
//set up the year and yearend variables...girls and boys will be based on time period of the season..

if (isset($_REQUEST['post'])) {
}

foreach (array('AAAA', 'AAA', 'AA') as $class) {
	$results = School::loadSchools($class);
	echo '<h4>'.$class.'</h4>';
	echo '<table>';  
	foreach ($results as $school) {
		echo '<tr><td><a href="team.php?teamId='.$school->schoolId.'">'.$school->school.'</a></td></tr>';
	}
	echo '</table>';  
}


?>

</body>