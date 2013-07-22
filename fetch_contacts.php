<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

global $db;

if (array_key_exists ('term', $_GET) == true) {
	$term = $db->real_escape_string ($_GET ['term']);
	$query = "SELECT id, name FROM contacts WHERE name LIKE '%$term%'";
	$results = $db->query ($query);

	$ret = array ();

	while ($row = $results->fetch_array ()) {
		$tmp = new stdClass ();
		$tmp->id = $row ['id'];
		$tmp->name = $row ['name'];
		$tmp->value = $row ['name'];
		$ret [] = $tmp;
	}

	echo json_encode ($ret);
}
else if (array_key_exists ('action', $_GET) == true) {
	switch ($_GET ['action']) {
		case 'list':
			$ids = explode ('::', $_GET ['having']);
			if (count ($ids) == 0)
				$ids [] = -1;

			$ret = array ();
			$query = "SELECT * FROM contacts WHERE id NOT IN (" . join (', ', $ids) . ")";

			$results = $db->query ($query);
			while ($row = $results->fetch_array ()) {
				$c = contact_to_json ($row, false);
				$ret [] = $c;
			}

			echo json_encode ($ret);
			break;

		case 'complete':
			$id = $db->real_escape_string ($_GET ['id']);
			$query = "SELECT * FROM contacts WHERE id = $id";
			$results = $db->query ($query);
			$row = $results->fetch_array ();
			$ret = contact_to_json ($row, false);

			if (array_key_exists ('statstart', $_GET) == true && array_key_exists ('statend', $_GET) == true) {
				$s = $db->real_escape_string ($_GET ['statstart']);
				$e = $db->real_escape_string ($_GET ['statend']);
				$ret->stats = contact_stats ($id, $s, $e);
			}

			echo json_encode ($ret);
			break;

		case 'remove':
			$id = $db->real_escape_string ($_GET ['id']);
			$db->autocommit (false);
			$query = "DELETE FROM events WHERE owner = $id";
			exec_nr_query ($query);
			$query = "DELETE FROM contacts WHERE id = $id";
			exec_nr_query ($query);
			$db->commit ();
			break;

		case 'save':
			$id = manage_contact ($_POST ['contact']);
			if ($id === false)
				echo '-1';
			else
				echo $id;

			break;
	}
}

?>
