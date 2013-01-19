<?php

require_once ('login.php');
require_once ('utils.php');

global $current_permissions;
if ($current_permissions == 0)
	exit ();

global $db;

$eventid = $_GET ['id'];

switch ($_GET ['action']) {
	case 'day':
		$tmp = new stdClass ();

		$query = "SELECT events.id AS id, events.title AS title, events.type AS type, events.category AS category, events.public AS public,
					eventdates.id AS dayid, DATE(eventdates.startdate) AS day, DATE_FORMAT(eventdates.startdate, '%H:%i') AS shour, DATE_FORMAT(eventdates.enddate, '%H:%i') AS ehour,
					events.price as price, events.partprice as partprice, events.paystatus as paystatus
				FROM events, eventdates
				WHERE eventdates.id = $eventid AND
					eventdates.eventid = events.id";
		$result = $db->query ($query);
		$date = $result->fetch_array ();
		$tmp->id = $date ['id'];
		$tmp->dayid = $date ['dayid'];
		$tmp->title = $date ['title'];
		$tmp->type = $date ['type'];
		$tmp->category = $date ['category'];
		$tmp->private_event = $date ['public'] == 0 ? true : false;
		$tmp->contact = contact_by_event ($tmp->id, false);
		$tmp->start = date_dbtoform ($date ['day']);
		$tmp->shour = $date ['shour'];
		$tmp->ehour = $date ['ehour'];
		$tmp->rooms = get_rooms_by_eventdate ($tmp->dayid);
		$tmp->materials = get_materials_by_eventdate ($tmp->dayid);
		$tmp->price = $date ['price'];
		$tmp->partprice = $date ['partprice'];
		$tmp->paystatus = $date ['paystatus'];

		echo json_encode ($tmp);
		break;

	case 'removeday':
		remove_event_day ($eventid);
		break;

	case 'removeevent':
		remove_event_days ($eventid);

		$query = "DELETE FROM events WHERE id = $eventid";
		exec_nr_query ($query);

		break;
}

?>
