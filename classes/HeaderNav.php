<?php
class HeaderNav {

	public static function stream($item, $css = null, $js = null, $wideClass = null) {

		if (!$js) { $js = array(); }
		echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
		echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
		echo '<link rel="stylesheet" href="/scripts/wisca-modern.css">'."\n";
		if ($css) {
			foreach ($css as $ss) {
				if (strpos($ss, 'yui') !== false) { continue; }
				echo '<link rel="stylesheet" type="text/css" href="'.$ss.'" />'."\n";
			}
		}
		foreach ($js as $j) {
			echo '<script type="text/javascript" src="'.$j.'"></script>'."\n";
		}
		echo '<title>WISCA - '.$item.'</title>';
		echo '</head><body>';

		$user = Session::getSession()->user;

		echo '<header id="wisca-header">';
		echo '<a class="logo" href="/">WISCA</a>';
		echo '<nav id="wisca-nav">';
		// public site links
		echo '<a href="/">Home</a>';
		echo '<span class="nav-dropdown"><a href="#">Results</a><ul>';
		echo '<li><a href="/results/state.php">State</a></li>';
		echo '<li><a href="/results/district.php">District</a></li>';
		echo '<li><a href="/results/league.php">League</a></li>';
		echo '</ul></span>';
		echo '<a href="/state-info.php">State Info</a>';
		// divider
		echo '<span class="nav-divider"></span>';
		// member links
		if (!$user->email) {
			self::wrap('Register', "account.php?register=1", $item !== 'Account');
			self::wrap('Log In', "account.php", $item !== 'Log in');
		} else {
			self::wrap('Account', "account.php", $item !== 'Account');
			if ($user->member) {
				self::wrap('Top Times', "toptimes.php", $item !== 'View Times');
			}
			self::wrap('Submit', "submit.php", $item !== 'Submit Time');
			self::wrap('My Times', "mysubmissions.php", $item !== 'Submissions');
			self::wrap('Teams', "teams.php", $item !== 'Teams');
			if ($user->admin || $user->member) {
				self::wrap('Users', "editusers.php", $item !== 'Edit Users');
			}
			self::wrap('Logout', "reg.php?logout&rd=account.php", $item !== 'Logout');
		}
		echo '</nav></header>';
		echo '<script>document.querySelectorAll(".nav-dropdown > a").forEach(function(a){a.addEventListener("click",function(e){e.preventDefault();var d=this.parentNode;var wasOpen=d.classList.contains("open");document.querySelectorAll(".nav-dropdown").forEach(function(x){x.classList.remove("open");});if(!wasOpen){d.classList.add("open");}});});document.addEventListener("click",function(e){if(!e.target.closest(".nav-dropdown")){document.querySelectorAll(".nav-dropdown").forEach(function(x){x.classList.remove("open");});}});</script>';
		$cls = $wideClass ? ' class="' . $wideClass . '"' : '';
		echo '<main id="wisca-content"'.$cls.'><div class="card">';
	}

	private static function wrap($text, $url, $anchor) {
		if ($anchor) {
			echo '<a href="'.$url.'">'.$text.'</a>';
		} else {
			echo '<a href="'.$url.'" class="active">'.$text.'</a>';
		}
	}

}

?>