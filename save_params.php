<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

function save_setting ($name, $val) {
	global $db;

	$query = "SELECT id FROM config WHERE name = '$name'";
	$result = $db->query ($query);

	if ($result->num_rows == 0)
		$query = "INSERT INTO config (name, value) VALUES ('$name', '$val')";
	else
		$query = "UPDATE config SET value = '$val' WHERE name = '$name'";

	return $db->query ($query);
}

function manage_setting ($name) {
	if (array_key_exists ($name, $_POST) == true) {
		$val = $_POST [$name];
		return save_setting ($name, $val);
	}
	else {
		return true;
	}
}

function manage_setting_empty ($name) {
	if (array_key_exists ($name, $_POST) == true && $_POST [$name] != '') {
		$val = $_POST [$name];
		return save_setting ($name, $val);
	}
	else {
		return true;
	}
}

function manage_setting_bool ($name) {
	if (array_key_exists ($name, $_POST) == true)
		return save_setting ($name, 'true');
	else
		return save_setting ($name, 'false');
}

$success = true;

$success = manage_setting ('startyear') || $success;
$success = manage_setting ('endyear') || $success;
$success = manage_setting ('fontsize') || $success;
$success = manage_setting ('pagesize') || $success;
$success = manage_setting ('mailaddress') || $success;
$success = manage_setting ('smtpserver') || $success;
$success = manage_setting ('smtpserverport') || $success;
$success = manage_setting ('smtpusername') || $success;
$success = manage_setting_empty ('smtppassword') || $success;
$success = manage_setting_bool ('smtpssl') || $success;

if ($success == true)
	$message = "Parametri salvati correttamente.";
else
	$message = "Si e' verificato un errore durante il salvataggio dei parametri.";

saved_page ($message);

?>

