<?php

$host_db = 'localhost';
$user_db = 'cdq';
$pass_db = 'cdq';
$work_db = 'cdq';

global $db;
$db = new mysqli ($host_db, $user_db, $pass_db, $work_db) or die ("Errore nella connessione con il database");

function db_get_value ($query) {
	global $db;

	$result = $db->query ($query) or die ("Impossibile eseguire query: " . $db->error);
	$array = $result->fetch_array ();
	$ret = $array [0];
	unset ($array);
	unset ($result);
	return $ret;
}

function exec_query ($query) {
	global $db;

	$result = $db->query ($query);

	if ($result == false) {
		echo "Errore: " . $db->error . " / " . $query;
		$db->rollback ();
		exit ();
	}

	return $result;
}

function exec_nr_query ($query) {
	global $db;

	if ($db->query ($query) == false) {
		echo "Errore: " . $db->error . " / " . $query;
		$db->rollback ();
		exit ();
	}
}

function date_dbtoform ($date) {
	if ($date == '0000-00-00') {
		return '';
	}
	else {
		list ($year, $month, $day) = explode ('-', $date);
		$day = str_pad ($day, 2, '0', STR_PAD_LEFT);
		$month = str_pad ($month, 2, '0', STR_PAD_LEFT);
		return "$day/$month/$year";
	}
}

function date_formtodb ($date) {
	if ($date == '') {
		return '0000-00-00';
	}
	else {
		list ($day, $month, $year) = explode ('/', $date);
		return "$year-$month-$day";
	}
}

function build_query ($get_params, $cols) {
	$where = array ();
	$where [] = 'id > 0';

	if ($get_params == false) {
		$sorting = 'id';
		$sorting_direction = 'desc';
	}
	else {
		$sorting = $_GET ['sorting'];
		$sorting_direction = $_GET ['sorting_direction'];

		foreach ($cols as $c) {
			$n = $c ['dbname'];
			$t = $c ['type'];

			if ($t == 'date') {
				if (array_key_exists ("${n}_from", $_GET))
					$where [] = "$n > '" . date_formtodb ($_GET ["${n}_from"]) . "'";
				if (array_key_exists ("${n}_to", $_GET))
					$where [] = "$n < '" . date_formtodb ($_GET ["${n}_to"]) . "'";
			}
			else if (array_key_exists ($n, $_GET)) {
				if ($t == 'string') {
					$where [] = "LOWER($n) LIKE '%" . strtolower ($_GET [$n]) . "%'";
				}
				else if ($t == 'boolean') {
					if ($_GET [$n] != 'both')
						$where [] = "$n = " . $_GET [$n];
				}
				else if ($t == 'number' && array_key_exists ("${n}_cmp", $_GET)) {
					switch ($_GET ["${n}_cmp"]) {
						case 'equal':
							$op = '=';
							break;
						case 'minor':
							$op = '<';
							break;
						case 'major':
							$op = '>';
							break;
					}

					$where [] = "$n $op " . $_GET [$n];
				}
				else if ($t == 'file') {
					if ($_GET [$n] != 'both')
						$where [] = "$n = " . $_GET [$n];
				}
			}
		}
	}

	return array ($where, $sorting, $sorting_direction);
}

?>
