<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

function manage_array ($postname, $tablename) {
	global $db;

	$success = true;

	$event_cats = explode ('###', $_POST [$postname]);
	$ids = array ();

	foreach ($event_cats as $ec) {
		list ($id, $name) = explode ('=', $ec);

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

$success = false;

if (array_key_exists ('events', $_POST) == true)
	$success = manage_array ('events', 'eventcategories');

if ($success == true && array_key_exists ('contacts', $_POST) == true)
	$success = manage_array ('contacts', 'contactcategories');

if ($success == true)
	$message = "Categorie salvate correttamente.";
else
	$message = "Si e' verificato un errore durante il salvataggio delle categorie: " . $db->error . ", $query.";

saved_page ($message);

?>

