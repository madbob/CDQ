<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

global $db;

if (array_key_exists ('action', $_GET) == true) {
	if ($_GET ['action'] == 'sorting') {
		$position = 0;

		foreach ($_POST ['sorting'] as $id) {
			$query = "UPDATE rooms SET position = $position WHERE id = $id";
			$db->query ($query);
			$position++;
		}
	}
	else if ($_GET ['action'] == 'remove') {
		$query = "DELETE FROM rooms WHERE id = " . $_POST ['id'];
		$db->query ($query);
	}
}
else {
	$success = true;
	$ids = array ();

	foreach ($_POST as $key => $value) {
		if (strncmp ($key, 'name_', 5) == 0) {
			list ($useless, $id) = explode ('_', $key);

			$name = $_POST [$key];
			$prices = $_POST ['price_' . $id];
			$pricelabels = $_POST ['pricelabel_' . $id];
			$visible = (array_key_exists ('visible_' . $id, $_POST) == true ? 'true' : 'false');

			$query = "SELECT id FROM rooms WHERE id = $id";
			$result = $db->query ($query);

			if ($result->num_rows == 0) {
				$query = "SELECT MAX(position) + 1 AS position FROM rooms";
				$position = 0; // db_get_value ($query);

				$query = "INSERT INTO rooms (name, visible, position) VALUES ('$name', $visible, $position)";
				if ($db->query ($query) == false) {
					$success = false;
					break;
				}

				$id = $db->insert_id;
				$ids [] = $id;
			}
			else {
				$query = "UPDATE rooms SET name = '$name', defaultprice = '$price', visible = $visible WHERE id = $id";
				if ($db->query ($query) == false) {
					$success = false;
					break;
				}

				$ids [] = $id;

				$query = "DELETE FROM roomprices WHERE roomid = $id";
				$db->query ($query);
			}

			for ($i = 0; $i < count ($prices); $i++) {
				$price = $prices [$i];
				$label = $pricelabels [$i];
				$query = "INSERT INTO roomprices (roomid, label, amount) VALUES ($id, '$label', $price)";
				$db->query ($query);
			}
		}
	}

	$query = "UPDATE rooms SET visible = false WHERE id NOT IN (" . join (', ', $ids) . ")";
	$db->query ($query);

	$query = "SELECT COUNT(id) FROM rooms WHERE visible = true";
	$num = db_get_value ($query);

	/*
		Questa formula e' inventata di sana pianta e
		ricavata in modo empirico.
		Occorre tener conto non solo del numero di
		casella ma anche (e soprattutto) del loro
		padding, che sfalsa tutti i conti
	*/
	$query = "UPDATE rooms SET width = " . (round ((100 - 5) / $num, 2) - 0.1) . " WHERE visible = true";
	$db->query ($query);

	if ($success == true)
		$message = "Sale salvate correttamente.";
	else
		$message = "Si e' verificato un errore durante il salvataggio delle sale: " . $db->error . ", $query.";

	saved_page ($message);
}

?>

