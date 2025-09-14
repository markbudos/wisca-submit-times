<?php
class HeaderNav {

	public static function stream($item, $css = null, $js = null) {

		if (!$css) { $css = array("/scripts/yui/fonts-min.css"); }
		if (!$js) { $js = array(); }
		echo '<html><head>';
		foreach ($css as $ss) {
			echo '<link rel="stylesheet" type="text/css" href="'.$ss.'" />'."\n";
		}
		foreach ($js as $j) {
			echo '<script type="text/javascript" src="'.$j.'"></script>'."\n";
		}
		echo '<title>WISCA - '.$item.'</title>';
		echo '</head><body class="yui-skin-sam">';

		$user = Session::getSession()->user;
		echo '<a href="/">WISCA Home</a> | ';
		if (!$user->email) {
			self::wrap('Register', "account.php?register=1", $item != 'Account', false);
		} else {
			self::wrap('Account', "account.php", $item != 'Account', true);
			if ($user->admin) {
				self::wrap('Edit Users', "editusers.php", $item != 'Edit Users', true);
			} else if ($user->member) {
				self::wrap('User List', "editusers.php", $item != 'Edit Users', true);
			}
			if ($user->member) {
				self::wrap('View Times', "toptimes.php", $item != 'View Times', true);
			}
			self::wrap('Submit Time', "submit.php", $item != 'Submit Time', true);
			self::wrap('Submissions', "mysubmissions.php", $item != 'Submissions', true);
			self::wrap('Teams', "teams.php", $item != 'Teams', true);
			self::wrap('Logout', "reg.php?logout&rd=account.php", $item != 'Logout', false);
		}
	}

	private static function wrap($text, $url, $anchor, $sep) {
		if ($anchor) { echo '<a href="'.$url.'">'; }
		echo $text;
		if ($anchor) { echo '</a>'; }
		if ($sep) {
			echo ' | ';
		}
	}

}


?>