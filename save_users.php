<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

global $db;

$ids = array ();

foreach ($_POST ['users'] as $user) {
	$id = $user->id;
	$username = $user->username;
	$password = $user->password;
	$permissions = $user->permission;

	if ($id == 'new') {
	}
	else {
		$query = "UPDATE users SET username = '$username', permissions = $permissions";
		if ($password != '')
			$query .= ", password = ''";
		$query .= "WHERE id = $id";
		$db->query ($query);

		$ids [] = $id;
	}
}

?>

