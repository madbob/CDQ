<?php

require_once ('db.php');

function perform_authentication ($userid) {
	global $db;

	$t = time ();

	/*
		tutte le sessioni piu' vecchie di una settimana sono eliminate
	*/

	$old_now = date ("Y-m-d", ($t - (60 * 60 * 24 * 7)));
	$query = sprintf ("DELETE FROM current_sessions
				WHERE init < '%s' OR
					userid = %d", $old_now, $userid);
	$db->query ($query) or die ("Impossibile sincronizzare sessioni");

	do {
		$session_id = substr (md5 (time ()), 0, 20);
		$query = sprintf ("SELECT * FROM current_sessions WHERE session_id = '%s'", $session_id);
		$result = $db->query ($query) or die ("Impossibile salvare sessione");
		$rows = $result->num_rows;
		unset ($result);
	} while ($rows != 0 && sleep (1) == 0);

	$expiry = time () + 60 * 60 * 24 * 30;
	$now = date ("Y-m-d", $expiry);

	$query = sprintf ("INSERT INTO current_sessions (session_id, init, userid)
				VALUES ( '%s', DATE('%s'), %d)",
					$session_id, $now, $userid);
	$db->query ($query) or die ("Impossibile salvare la sessione");

	$session_serial = $session_id . '-' . $_SERVER ['REMOTE_ADDR'];
	$session_hash = md5 ($session_serial);
	$session_cookie = base64_encode ($session_serial) . '-*-' . $session_hash;

	setcookie ('cdq', $session_cookie, $expiry, '/', '', 0 );
}

function parse_session_data () {
	if (array_key_exists ( 'cdq', $_COOKIE ) == false)
		return false;

	$session_data = $_COOKIE ['cdq'];

	list ($session_serial, $hash) = explode ('-*-', $session_data);
	$session_serial = base64_decode ($session_serial);
	$new_hash = md5 ($session_serial);

	if ($hash != $new_hash) {
		setcookie ('cdq', "", 0, '/', '', 0);
		return false;
	}

	list ($session_id, $ip) = explode ('-', $session_serial, 4);

	if ($ip != $_SERVER ['REMOTE_ADDR']) {
		setcookie ('cdq', "", 0, '/', '', 0);
		return false;
	}

	/*
		lo User-Agent non e' volutamente controllato
	*/

	return $session_id;
}

function check_session () {
	global $autokey;
	global $current_permissions;

	if (array_key_exists ('autokey', $_GET) == true && $_GET ['autokey'] == $autokey) {
		$current_permissions = 0;
		return true;
	}

	global $db;

	$current_session_id = parse_session_data ();
	if ($current_session_id == false)
		return false;

	$query = sprintf ("SELECT users.* FROM users, current_sessions
				WHERE current_sessions.session_id = '%s' AND
					current_sessions.userid = users.id", $current_session_id);
	$result = $db->query ($query) or die ("Impossibile eseguire query: " . $db->error);

	if ($result->num_rows == 0) {
		setcookie ('cdq', "", 0, '/', '', 0);
		return false;
	}

	$array = $result->fetch_array ();
	$current_permissions = $array ['permissions'];
	return true;
}

if (check_session () == false) {
	global $db;
	$done = false;

	if (array_key_exists ('action', $_GET) && $_GET ['action'] == 'do') {
		$query = sprintf ("SELECT password, id FROM users WHERE username = '%s'", $_POST ['username']);
		$result = $db->query ($query);

		if ($result->num_rows != 0) {
			$array = $result->fetch_array ();
			if ($array ['password'] == md5 ($_POST ['password'])) {
				perform_authentication ($array ['id']);
				$done = true;
				header ('Location: index.php');
			}
		}
	}

	if ($done == false) {
		?>

		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

			<title>CDQ - Login</title>

			<link rel="stylesheet" href="main.css.php" />
		</head>

		<body>
			<img src="img/logo.png" alt="cdq" class="login_logo" />

			<form method="POST" action="login.php?action=do" class="login_form">
				<fieldset>
					<p>
						<label for="username">Username</label>
						<input type="text" name="username" />
					</p>
					<p>
						<label for="password">Password</label>
						<input type="password" name="password" />
					</p>
					<p class="submit">
						<input type="submit" value="Login" />
					</p>
				</fieldset>
			</form>
		</body>

		<?php

		exit ();
	}
}
else {
	if (array_key_exists ('action', $_GET) && $_GET ['action'] == 'logout')
		setcookie ('cdq', "", 0, '/', '', 0);
}

?>
