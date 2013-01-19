<?php

/*
	GENERIC VALUES
*/

global $minhour;
global $maxhour;
$minhour = 9;
$maxhour = 23;

/***********************************************************************
	DATABASE
*/

function get_details_by_eventdate ($id, $type) {
	global $db;

	$query = "SELECT ${type}id FROM event${type}s WHERE eventdateid = " . $id;
	$result = $db->query ($query);

	$ret = array ();
	while ($r = $result->fetch_array ())
		$ret [] = $r [$type . 'id'];

	return $ret;
}

function save_details_by_eventdate ($day, $type) {
	if (array_key_exists ($type . 's', $day) == false)
		return;

	global $db;

	$id = $day ['dayid'];

	$current = get_details_by_eventdate ($id, $type);
	$updated = array ();

	foreach ($day [$type . 's'] as $a) {
		if ($a == -1)
			continue;

		if (array_search ((int) $a, $current) == false) {
			$query = "INSERT INTO event${type}s (eventdateid, ${type}id) VALUES ($id, $a)";
			exec_nr_query ($query);
		}

		$updated [] = $a;
	}

	if (count ($updated) == 0)
		$query = "DELETE FROM event${type}s WHERE eventdateid = $id";
	else
		$query = "DELETE FROM event${type}s WHERE eventdateid = $id AND ${type}id NOT IN (" . join (', ', $updated) . ")";

	exec_nr_query ($query);

	unset ($updated);
	unset ($current);
}

function getconf ($conf) {
	$query = "SELECT value FROM config WHERE name = '$conf'";
	return db_get_value ($query);
}

/***********************************************************************
	ROOMS
*/

function retrieve_rooms ($all = true) {
	global $db;

	$cols = array ();
	$query = "SELECT * FROM rooms " . ($all == false ? "WHERE visible = true" : "") . " ORDER BY position ASC";
	$result = $db->query ($query);
	while ($col = $result->fetch_array ())
		$cols [] = $col;

	return $cols;
}

function get_room_price ($id) {
	global $roomPrices;

	if (isset ($roomPrices) == false)
		$roomPrices = array ();

	if (array_key_exists ($id, $roomPrices) == false) {
		global $db;

		$query = "SELECT defaultprice FROM rooms WHERE id = $id";
		$result = $db->query ($query);
		$col = $result->fetch_array ();
		$roomPrices [$id] = $col [0];
	}

	return $roomPrices [$id];
}

function get_rooms_by_eventdate ($id) {
	return get_details_by_eventdate ($id, 'room');
}

function save_rooms_by_eventdate ($day) {
	save_details_by_eventdate ($day, 'room');
}

function single_room_selector ($s = -1, $results = null, $j = false, $add = false) {
	if ($results == null) {
		global $db;

		$query = "SELECT * FROM rooms ORDER BY position";
		$results = $db->query ($query);
	}

	/*
		Formattazione esotica per permettere di includere questo
		frammento di HTML nel Javascript
	*/

	?><div class="controls"> <?php sj($j) ?>
		<select name="room"> <?php sj($j) ?>
			<?php while ($row = $results->fetch_array ()): ?> <?php sj($j) ?>
			<option <?php if ($s == $row ['id']) echo 'selected="selected" ' ?>value="<?php echo $row ['id'] ?>"><?php echo $row ['name'] ?></option> <?php sj($j) ?>
			<?php endwhile; $results->data_seek (0); ?> <?php sj($j) ?>
		</select> <?php sj($j) ?> <?php sj($j) ?>
		<?php if ($add == true): ?>
		<img src="img/add.png" class="addroom" /> <?php sj($j) ?>
		<?php else: ?>
		<img src="img/remove.png" class="removeroom" /> <?php sj($j) ?>
		<?php endif; ?>
	</div><?php
}

function rooms_selector ($sel = '') {
	global $db;

	$selected = explode (',', $sel);

	$query = "SELECT * FROM rooms ORDER BY position";
	$results = $db->query ($query);

	?>

	<div class="roomsel">
		<div class="control-group">
			<label class="control-label" for="room">Sala</label>
			<?php

			$addable = true;

			foreach ($selected as $s) {
				single_room_selector ($s, $results, false, $addable);
				$addable = false;
			}

			?>
		</div>
	</div>

	<?php
}

function rooms_selector_by_eventdate ($id) {
	global $db;

	$rooms = array ();

	$query = "SELECT roomid FROM eventrooms WHERE eventdateid = $id";
	$results = $db->query ($query);

	while ($r = $results->fetch_array ())
		$rooms [] = $r ['roomid'];

	rooms_selector (join (',', $rooms));
}

/***********************************************************************
	MATERIALS
*/

function get_materials_by_eventdate ($id) {
	return get_details_by_eventdate ($id, 'material');
}

function save_materials_by_eventdate ($day) {
	save_details_by_eventdate ($day, 'material');
}

function single_material_selector ($s = -1, $results = null, $j = false, $add = false) {
	if ($results == null) {
		global $db;

		$query = "SELECT * FROM materials ORDER BY name ASC";
		$results = $db->query ($query);
	}

	/*
		Formattazione esotica per permettere di includere questo
		frammento di HTML nel Javascript
	*/

	?><div class="controls"> <?php sj($j) ?>
		<select name="material"> <?php sj($j) ?>
			<option value="-1"></option> <?php sj($j) ?>
			<?php while ($row = $results->fetch_array ()): ?> <?php sj($j) ?>
			<option <?php if ($s == $row ['id']) echo 'selected="selected" ' ?>value="<?php echo $row ['id'] ?>"><?php echo $row ['name'] ?></option> <?php sj($j) ?>
			<?php endwhile; $results->data_seek (0); ?> <?php sj($j) ?>
		</select> <?php sj($j) ?> <?php sj($j) ?>
		<?php if ($add == true): ?>
		<img src="img/add.png" class="addmaterial" /> <?php sj($j) ?>
		<?php else: ?>
		<img src="img/remove.png" class="removematerial" /> <?php sj($j) ?>
		<?php endif; ?>
	</div><?php
}

function materials_selector ($sel = '') {
	global $db;

	$selected = explode (',', $sel);

	$query = "SELECT * FROM materials ORDER BY name ASC";
	$results = $db->query ($query);

	?>

	<div class="materialsel">
		<div class="control-group">
			<label class="control-label" for="material">Materiali</label>
			<?php

			$addable = true;

			foreach ($selected as $s) {
				single_material_selector ($s, $results, false, $addable);
				$addable = false;
			}

			?>
		</div>
	</div>

	<?php
}

/***********************************************************************
	TIMES
*/

function day_hidden_id ($id) {
	?>

	<input type="hidden" name="dayid" value="<?php echo $id ?>" />

	<?php
}

function day_selector ($day = '', $removable = false) {
	?>

	<div class="control-group">
		<label class="control-label" for="start">Giorno</label>
		<div class="controls">
			<input type="text" name="start" class="date" value="<?php echo $day ?>" />
			<?php if ($removable == true): ?>
			&nbsp;<img src="img/remove.png" class="removeday" />
			<?php endif; ?>
		</div>
	</div>

	<?php
}

function hour_selector ($shour = '', $ehour = '') {
	?>

	<div class="control-group">
		<label class="control-label" for="shour">Dalle ore</label>
		<div class="controls">
			<input type="text" name="shour" class="hour span1" value="<?php echo $shour ?>" /> <span>alle ore</span> <input type="text" name="ehour" class="hour span1" value="<?php echo $ehour ?>" />
		</div>
	</div>

	<?php
}

function weekday_selector ($sel = -1) {
	?>

	<div class="control-group">
		<label class="control-label" for="weekday">Giorno Settimana</label>
		<div class="controls">
			<select name="weekday">
				<?php for ($i = 1; $i < 8; $i++): ?>
				<option value="<?php echo $i ?>"<?php if ($sel == $i) echo ' selected="selected"' ?>><?php echo weekday ($i) ?></option>
				<?php endfor; ?>
			</select>
		</div>
	</div>

	<?php
}

function recurrence_selector ($days = 1, $onmonth = 0, $onweek = 4) {
	?>

	<div class="control-group">
		<label class="control-label">Giorni Settimanali</label>
		<div class="controls">
			<div data-toggle="buttons-radio" class="btn-group iterations">
				<?php for ($i = 1; $i < 8; $i++): ?>
				<button type="button" class="btn<?php if ($days == $i) echo ' active' ?>"><?php echo $i ?></button>
				<?php endfor; ?>
			</div>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label">Settimana del Mese</label>
		<div class="controls">
			<div data-toggle="buttons-radio" class="btn-group monthpos">
				<button type="button" class="btn<?php if ($onmonth == 0) echo ' active' ?>">Tutte</button>
				<button type="button" class="btn<?php if ($onmonth == 1) echo ' active' ?>">1&ordf;</button>
				<button type="button" class="btn<?php if ($onmonth == 2) echo ' active' ?>">2&ordf;</button>
				<button type="button" class="btn<?php if ($onmonth == 3) echo ' active' ?>">3&ordf;</button>
				<button type="button" class="btn<?php if ($onmonth == 4) echo ' active' ?>">4&ordf;</button>
				<button type="button" class="btn<?php if ($onmonth == 5) echo ' active' ?>">Ultima</button>
				<button type="button" class="btn<?php if ($onmonth == 6) echo ' active' ?>">Ignora</button>
			</div>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label">Settimane</label>
		<div class="controls">
			<div data-toggle="buttons-radio" class="btn-group weekpos">
				<button type="button" class="btn<?php if ($onweek == 0) echo ' active' ?>">Tutte</button>
				<button type="button" class="btn<?php if ($onweek == 1) echo ' active' ?>">ogni 2</button>
				<button type="button" class="btn<?php if ($onweek == 2) echo ' active' ?>">ogni 3</button>
				<button type="button" class="btn<?php if ($onweek == 3) echo ' active' ?>">ogni 4</button>
				<button type="button" class="btn<?php if ($onweek == 4) echo ' active' ?>">Ignora</button>
			</div>
		</div>
	</div>

	<?php
}

function weekday ($i) {
	switch ($i) {
		case '1':
			return 'Lunedi';
			break;

		case '2':
			return 'Martedi';
			break;

		case '3':
			return 'Mercoledi';
			break;

		case '4':
			return 'Giovedi';
			break;

		case '5':
			return 'Venerdi';
			break;

		case '6':
			return 'Sabato';
			break;

		case '7':
			return 'Domenica';
			break;
	}
}

function weekday_en ($i) {
	switch ($i) {
		case '1':
			return 'monday';
			break;

		case '2':
			return 'tuesday';
			break;

		case '3':
			return 'wednesday';
			break;

		case '4':
			return 'thursday';
			break;

		case '5':
			return 'friday';
			break;

		case '6':
			return 'saturday';
			break;

		case '7':
			return 'sunday';
			break;
	}
}

function monthname ($i) {
	switch ($i) {
		case 1:
			return 'Gennaio';
			break;

		case 2:
			return 'Febbraio';
			break;

		case 3:
			return 'Marzo';
			break;

		case 4:
			return 'Aprile';
			break;

		case 5:
			return 'Maggio';
			break;

		case 6:
			return 'Giugno';
			break;

		case 7:
			return 'Luglio';
			break;

		case 8:
			return 'Agosto';
			break;

		case 9:
			return 'Settembre';
			break;

		case 10:
			return 'Ottobre';
			break;

		case 11:
			return 'Novembre';
			break;

		case 12:
			return 'Dicembre';
			break;
	}
}

function compute_cyclic_days ($repeats) {
	global $db;

	if ($repeats == -1) {
		/*
			Se l'evento permanente viene definito ad anno
			iniziato, comincia dalla data odierna
		*/
		$startyear = strtotime (date_formtodb (getconf ('startyear')));
		if ($startyear < time ())
			$start = date ('Y-m-d');
		else
			$start = date ('Y-m-d', $startyear);

		$finaldate = strtotime (date_formtodb (getconf ('endyear')));
	}
	else {
		$start = date_formtodb ($_GET ['start']);
		$finaldate = -1;
	}

	$weekdays_tmp = explode (';', $_GET ['weekdays']);
	$weekdays = array ();
	foreach ($weekdays_tmp as $tmp) {
		$tokens = explode ('|', $tmp);
		$wd ['day'] = $tokens [0];
		$wd ['shour'] = $tokens [1];
		$wd ['ehour'] = $tokens [2];
		$wd ['rooms'] = $tokens [3];
		$weekdays [] = $wd;
	}

	usort ($weekdays, 'sort_by_weekday');

	if (count ($weekdays) == 1) {
		$weekdays [0]['offset'] = 7;
	}
	else {
		$prev = 0;

		for ($i = 1; $i < count ($weekdays); $i++) {
			$wd = $weekdays [$i];

			$offset = $wd ['day'] - $weekdays [$prev]['day'];
			if ($offset == 0)
				$offset = 7;

			$weekdays [$prev]['offset'] = $offset;
			$prev = $i;
		}

		$weekdays [$prev]['offset'] = 7 - $weekdays [$prev]['day'] + $weekdays [0]['day'];
	}

	$check_wd = date ('N', strtotime ($start));
	if ($check_wd != $weekdays [0]['day'])
		$date = date ('Y-m-d', strtotime ($start . ' + ' . (7 - $weekdays [0]['day']) . ' days'));
	else
		$date = $start;

	/*
		Se e' stata definita una posizione nel mese (e.g.
		seconda settimana) e la data di partenza non coincide,
		si confronta la posizione nel mese prescelto. Se la data
		di partenza e' precedente va bene, altrimenti si prende
		quella del mese successivo
	*/
	list ($y, $m, $d) = explode ('-', $date);
	$check_pos = positional_in_month ($_GET ['onmonth'], $weekdays [0]['day'], $m, $y, $date);
	if ($check_pos != $date) {
		if (strtotime ($date) < strtotime ($check_pos))
			$date = $check_pos;
		else
			$date = positional_in_month ($_GET ['onmonth'], $weekdays [0]['day'], $m + 1, $y, $date);
	}

	$ret = array ();

	for ($i = 0; ($repeats > 1 && $i < $repeats) || ($finaldate > 1 && strtotime ($date) < $finaldate); $i++) {
		foreach ($weekdays as $wd) {
			$d = $date;

			$step = new stdClass ();
			$step->date = $d;
			$step->shour = $wd ['shour'];
			$step->ehour = $wd ['ehour'];
			$step->rooms = $wd ['rooms'];
			$ret [] = $step;

			$date = date ('Y-m-d', strtotime ($date . ' + ' . $wd ['offset'] . ' days'));
		}

		list ($y, $m, $d) = explode ('-', $date);
		$date = positional_in_month ($_GET ['onmonth'], $weekdays [0]['day'], $m + 1, $y, $date);

		$onweek = $_GET ['onweek'];
		switch ($onweek) {
			case 1:
			case 2:
			case 3:
				$date = date ('Y-m-d', strtotime ($date . " +$onweek weeks"));
				break;

			case 0:
			case 4:
			default:
				break;
		}
	}

	return $ret;
}

function positional_in_month ($pos, $weekday, $month, $year, $default) {
	switch ($pos) {
		case 1:
			$mj = 'first';
			break;

		case 2:
			$mj = 'second';
			break;

		case 3:
			$mj = 'third';
			break;

		case 4:
			$mj = 'fourth';
			break;

		case 5:
			$mj = 'last';
			break;

		case 0:
		case 6:
		default:
			$mj = null;
			break;
	}

	if ($mj != null)
		return date ('Y-m-d', strtotime ($mj . ' ' . weekday_en ($weekday), mktime (0, 0, 0, $month, 1, $year)));
	else
		return $default;
}

function sort_by_weekday ($first, $second) {
	if ($first ['day'] == $second ['day'])
		return $first ['shour'] - $second ['shour'];
	else
		return $first ['day'] - $second ['day'];
}

function do_week ($week) {
	global $db;
	global $minhour;
	global $maxhour;

	$date = $week;
	$weekdays = array ();
	$events = array ();

	for ($i = 0; $i < 7; $i++) {
		$o = date ('N', strtotime ($date));
		list ($y, $m, $d) = explode ('-', $date);
		$weekday = new stdClass ();
		$weekday->name = weekday ($o) . ' ' . $d;
		$d = str_pad ($d, 2, '0', STR_PAD_LEFT);
		$m = str_pad ($m, 2, '0', STR_PAD_LEFT);
		$weekday->date = "$d/$m/$y";
		$weekdays [] = $weekday;

		$query = "SELECT eventdates.id AS id, events.title AS title, events.type AS type, DATE(eventdates.startdate) AS day,
					HOUR(eventdates.startdate) AS start, MINUTE(eventdates.startdate) AS startmin,
					HOUR(eventdates.enddate) AS end, MINUTE(eventdates.enddate) AS endmin
				FROM events, eventdates
				WHERE DATE(eventdates.startdate) = '$date' AND
					eventdates.eventid = events.id
						ORDER BY eventdates.startdate DESC";
		$results = $db->query ($query) or die ("Impossibile eseguire query: " . $db->error);

		while ($d = $results->fetch_array ()) {
			$ev = new stdClass ();
			$ev->id = $d ['id'];
			$ev->name = $d ['title'];
			$ev->type = $d ['type'];
			$ev->day = date_dbtoform ($d ['day']);
			$ev->shour = $d ['start'] . ':' . str_pad ($d ['startmin'], 2, '0');
			$ev->ehour = $d ['end'] . ':' . str_pad ($d ['endmin'], 2, '0');
			$ev->rooms = get_rooms_by_eventdate ($d ['id']);
			$ev->materials = get_materials_by_eventdate ($d ['id']);
			$events [] = $ev;
		}

		$date = date ('Y-m-d', strtotime ($date . " + 1 day"));
	}

	$ret = new stdClass ();
	$ret->weekdays = $weekdays;
	$ret->events = $events;
	echo json_encode ($ret);
}

function room_properties_form ($id, $name, $price, $visible, $new = true) {
	?>

	<li id="properties_<?php echo $id ?>">
		<?php if ($new == false): ?>
		<input type="hidden" name="old_element" value="true" />
		<?php endif; ?>

		<fieldset>
			<div class="control-group">
				<label class="control-label" for="name_<?php echo $id ?>">Nome</label>
				<div class="controls">
					<input class="name_value" type="text" name="name_<?php echo $id ?>" value="<?php echo $name ?>" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="price_<?php echo $id ?>">Prezzo Orario</label>
				<div class="controls">
					<input type="text" name="price_<?php echo $id ?>" value="<?php echo $price ?>" /> €
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="visible_<?php echo $id ?>">Visibile</label>
				<div class="controls">
					<input type="checkbox" name="visible_<?php echo $id ?>" <?php if ($visible) echo ' checked="checked"' ?> />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="remove_<?php echo $id ?>">Rimuovi</label>
				<div class="controls">
					<input type="button" name="remove_<?php echo $id ?>" value="Elimina Sala" class="remove_column btn" />
				</div>
			</div>
		</fieldset>
	</li>

	<?php
}

function remove_event_day ($id) {
	$query = "DELETE FROM eventrooms WHERE eventdateid = $id";
	exec_nr_query ($query);
	$query = "DELETE FROM eventmaterials WHERE eventdateid = $id";
	exec_nr_query ($query);
	$query = "DELETE FROM eventdates WHERE id = $id";
	exec_nr_query ($query);
}

function remove_event_days ($id, $saved = null) {
	if ($saved != null)
		$query = "SELECT id FROM eventdates WHERE eventid = $id AND id NOT IN (" . join (', ', $saved) . ")";
	else
		$query = "SELECT id FROM eventdates WHERE eventid = $id";

	$result = exec_query ($query);

	while ($r = $result->fetch_array ()) {
		$broken = $r ['id'];
		remove_event_day ($broken);
	}
}

/***********************************************************************
	CONTACTS
*/

function contact_by_event ($eventid, $encode = true) {
	global $db;

	$query = "SELECT contacts.*
			FROM contacts, events
			WHERE events.id = $eventid AND contacts.id = events.owner";
	$result = $db->query ($query);
	$contact = $result->fetch_array ();

	return contact_to_json ($contact, $encode);
}

function contact_to_json ($dbrow, $encode = true) {
	$tmp = new stdClass ();
	$tmp->id = $dbrow ['id'];
	$tmp->name = $dbrow ['name'];
	$tmp->category = $dbrow ['category'];
	$tmp->mail = $dbrow ['mail'];
	$tmp->phone = $dbrow ['phone'];
	$tmp->web = $dbrow ['web'];
	$tmp->notes = $dbrow ['notes'];

	if ($encode == true)
		return json_encode ($tmp);
	else
		return $tmp;
}

function contact_edit_form () {
	global $db;

	?>

	<input type="hidden" name="contactid" value="new" />

	<div class="control-group">
		<label class="control-label" for="contactname">Nome</label>
		<div class="controls">
			<input type="text" name="contactname" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="contactcat">Categoria</label>
		<div class="controls">
			<select name="contactcat">
				<?php

				$query = "SELECT * FROM contactcategories ORDER BY name";
				$results = $db->query ($query);

				while ($row = $results->fetch_array ()) {
					?>

					<option value="<?php echo $row ['id'] ?>"><?php echo $row ['name'] ?></option>

					<?php
				}
				?>
			</select>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="contactphone">Telefono</label>
		<div class="controls">
			<input type="text" name="contactphone" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="contactmail">Mail</label>
		<div class="controls">
			<input type="text" name="contactmail" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="contactweb">Website</label>
		<div class="controls">
			<input type="text" name="contactweb" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="contactnotes">Note</label>
		<div class="controls">
			<textarea name="contactnotes"></textarea>
		</div>
	</div>

	<?php
}

function manage_contact ($contact) {
	global $db;

	$name = $contact ['name'];
	$category = $contact ['category'];
	$mail = $contact ['mail'];
	$phone = $contact ['phone'];
	$web = $contact ['web'];
	$notes = $contact ['notes'];

	if ($contact ['id'] == 'new') {
		$query = "INSERT INTO contacts (name, category, mail, phone, web, notes)
				VALUES ('$name', $category, '$mail', '$phone', '$web', '$notes')";

		if ($db->query ($query) == false) {
			echo "Errore: " . $db->error;
			return false;
		}

		$contactid = $db->insert_id;
	}
	else {
		$contactid = $contact ['id'];

		$query = "UPDATE contacts
				SET name = '$name', category = $category, mail = '$mail', phone = '$phone', web = '$web', notes = '$notes'
				WHERE id = $contactid";

		if ($db->query ($query) == false) {
			echo "Errore: " . $db->error;
			return false;
		}
	}

	return $contactid;
}

/***********************************************************************
	EXTRA
*/

function sj ($j) {
	if ($j == true)
		echo '\\';
}

function saved_page ($message) {
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="it">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta HTTP-EQUIV="REFRESH" content="5; url=index.php">

	<title>Calendario del Quartiere</title>

	<link rel="stylesheet" href="main.css.php" />
</head>

<body>
	<div class="saving_message">
		<p>
			<?php echo $message ?>
		</p>
		<p>
			Tra 5 secondi sarai reindirizzato automaticamente alla pagina principale, oppure <a href="index.php">clicca qui</a>.
		</p>
	</div>
</body>

	<?php
}

?>
