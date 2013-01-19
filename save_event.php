<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

global $db;

$contact = $_POST ['contact'];

$contactid = manage_contact ($contact);
if ($contactid === false)
	return;

$db->autocommit (false);

$id = $_POST ['id'];
$title = $_POST ['title'];
$category = $_POST ['category'];
$public = ($_POST ['private_event'] == 'false') ? 'true' : 'false';
$vat = true;
$price = $_POST ['price'];
$partprice = $_POST ['partprice'];
$paystatus = $_POST ['paystatus'];
$notes = '';

if ($id == 'new') {
	$type = $_POST ['type'];

	$query = "INSERT INTO events (type, title, owner, hasvat, category, public, price, partprice, paystatus, notes)
			VALUES ($type, '$title', $contactid, $vat, $category, $public, $price, $partprice, $paystatus, '$notes')";
	exec_nr_query ($query);

	$eventid = $db->insert_id;

	foreach ($_POST ['days'] as $day) {
		$d = date_formtodb ($day ['start']);
		$shour = $day ['shour'];
		$ehour = $day ['ehour'];

		$query = "INSERT INTO eventdates (eventid, startdate, enddate) VALUES ($eventid, '$d $shour', '$d $ehour')";
		exec_nr_query ($query);

		$eventdateid = $day ['dayid'] = $db->insert_id;
		save_rooms_by_eventdate ($day);
		save_materials_by_eventdate ($day);
	}
}
else {
	$eventid = $id;

	$query = "UPDATE events SET title = '$title', category = $category, public = $public,
			price = $price, partprice = $partprice, paystatus = $paystatus WHERE id = $eventid";
	exec_nr_query ($query);

	$ids = array ();

	foreach ($_POST ['days'] as $day) {
		$dayid = $day ['dayid'];
		$d = date_formtodb ($day ['start']);
		$shour = $day ['shour'];
		$ehour = $day ['ehour'];

		if ($dayid == -1) {
			$query = "INSERT INTO eventdates (eventid, startdate, enddate) VALUES ($eventid, '$d $shour', '$d $ehour')";
			exec_nr_query ($query);
			$dayid = $day ['dayid'] = $db->insert_id;
		}
		else {
			$query = "UPDATE eventdates SET startdate = '$d $shour', enddate = '$d $ehour' WHERE id = $dayid";
			exec_nr_query ($query);
		}

		save_rooms_by_eventdate ($day);
		save_materials_by_eventdate ($day);
		$ids [] = $dayid;
	}

	if ($_POST ['edittype'] == 'full')
		remove_event_days ($eventid, $ids);
}

if ($db->commit () === true)
	echo $eventid;

?>
