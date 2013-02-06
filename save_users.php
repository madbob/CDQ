<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

global $db;

$ids = array ();

foreach ($_POST ['users'] as $user) {
	$id = $user ['id'];
	$username = $user ['username'];
	$password = $user ['password'];
	$permissions = $user ['permission'];

	if ($username == '')
		continue;

	if ($id == 'new') {
		$query = "INSERT INTO users (username, permissions, password) VALUES ('$username', $permissions, '" . md5 ($password) . "')";
		$db->query ($query);
		$ids [] = $db->insert_id;
	}
	else {
		$query = "UPDATE users SET username = '$username', permissions = $permissions";
		if ($password != '')
			$query .= ", password = '" . md5 ($password) . "'";
		$query .= "WHERE id = $id";

		$db->query ($query);
		$ids [] = $id;
	}
}

$query = "DELETE FROM users WHERE id NOT IN (" . join (', ', $ids) . ")";
$db->query ($query);

?>

