<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

function manage_array ($postname, $tablename) {
	global $db;

	$success = true;

	$materials = explode ('###', $_POST [$postname]);
	$ids = array ();

	foreach ($materials as $m) {
		list ($id, $name) = explode ('=', $m);

		if ($id == 'new') {
			$query = "INSERT INTO $tablename (name) VALUES ('$name')";
			if ($db->query ($query) == false) {
				$success = false;
				break;
			}

			$ids [] = $db->insert_id;
		}
		else {
			$id = substr ($id, 3);

			$query = "UPDATE $tablename SET name = '$name' WHERE id = $id";
			if ($db->query ($query) == false) {
				$success = false;
				break;
			}

			$ids [] = $id;
		}
	}

	$query = "DELETE FROM $tablename WHERE id NOT IN (" . join (', ', $ids) . ")";
	if ($db->query ($query) == false) {
		$success = false;
		break;
	}

	return $success;
}

if (array_key_exists ('materials', $_POST) == true)
	$success = manage_array ('materials', 'materials');
else
	$success = false;

if ($success == true)
	$message = "Materiali salvati correttamente.";
else
	$message = "Si e' verificato un errore durante il salvataggio dei materiali: " . $db->error . ", $query.";

saved_page ($message);

?>

