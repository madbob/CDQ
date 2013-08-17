<?php

require_once ('login.php');
require_once ('utils.php');

if (array_key_exists ('type', $_GET) == false)
	exit ();

switch ($_GET ['type']) {
	case 'room_form':
		room_properties_form ($_GET ['id'], 'Senza Nome', 0, true);
		break;

	case 'page':
		echo json_encode (do_week ($_GET ['start']));
		break;

	case 'check_days':
		$days = compute_cyclic_days ($_GET ['repeats']);

		$classname = 'replicableday';
		$removable = false;

		foreach ($days as $day) {
			$d = $day->date;

			$query = "SELECT events.id, events.title FROM events, eventdates, eventrooms
					WHERE eventrooms.roomid IN (" . $day->rooms . ") AND
						eventrooms.eventdateid = eventdates.id AND
						DATE (eventdates.startdate) = '$d' AND
						TIME (eventdates.enddate) > TIME ('" . $day->shour . "') AND
						TIME (eventdates.startdate) < TIME ('" . $day->ehour . "')";

			$results = $db->query ($query);

			if ($results === false) {
				echo $query . ' = ' . $db->error . "\n";
				continue;
			}

			if ($results->num_rows != 0)
				$extra = ' warning';
			else
				$extra = '';

			unset ($results);

			?>

			<div class="<?php echo $classname ?>">
				<?php
				day_hidden_id ('');
				day_selector (date_dbtoform ($d), $removable);
				hour_selector ($day->shour, $day->ehour);
				rooms_selector ($day->rooms);
				?>

				<hr />
			</div>

			<?php

			$classname = 'dupreplicableday';
			$removable = true;
		}

		?>

		<a href="#" class="btn pull-right addday">Aggiungi Giorno</a>
		<a href="#" class="btn pull-right back_cycle">Rielabora</a>

		<?php

		break;

	case 'event_days':
		global $db;

		$query = "SELECT id, DATE(startdate) AS day, DATE_FORMAT(startdate, '%H:%i') AS shour, DATE_FORMAT(enddate, '%H:%i') AS ehour
				FROM eventdates
				WHERE eventid = " . $_GET ['id'];
		$result = $db->query ($query);

		$classname = 'replicableday';

		?>

		<p>
			Edita questa sezione per propagare le stesse correzioni a tutti i giorni, oppure modifica solo i giorni che servono.
			In entrambi i casi, ricorda di salvare!
		</p>

		<div class="propagate_modify">
			<?php
			hour_selector (0, 0);
			rooms_selector_by_eventdate (-1);
			?>

			<a href="#" class="btn pull-right modifyall">Modifica Tutto</a>
			<br />
			<hr />
		</div>

		<?php

		while ($date = $result->fetch_array ()) {
			?>

			<div class="<?php echo $classname ?>">
				<?php
				day_hidden_id ($date ['id']);
				day_selector (date_dbtoform ($date ['day']), true);
				hour_selector ($date ['shour'], $date ['ehour']);
				rooms_selector_by_eventdate ($date ['id']);
				?>

				<hr />
			</div>

			<?php

			$classname = 'dupreplicableday';
		}

		?>

		<a href="#" class="btn pull-right addday">Aggiungi Giorno</a>

		<?php

		break;

	case 'check_payment':
		global $db;

		if ($_POST ['id'] != 'new') {
			$managed = true;

			$query = "SELECT price, partprice, paystatus
					FROM events
					WHERE id = " . $_POST ['id'];
			$result = $db->query ($query);
			$data = $result->fetch_array ();

			$total = $data ['price'];
			$payed = $data ['partprice'];
			$status = $data ['paystatus'];

			$query = "SELECT id, HOUR(startdate) as sh, MINUTE(startdate) as sm, HOUR(enddate) as eh, MINUTE(enddate) as em
					FROM eventdates
					WHERE eventid = " . $_POST ['id'];
			$result = $db->query ($query);

			while ($data = $result->fetch_array ()) {
				$d ['shour'] = $data ['sh'] . ':' . $data ['sm'];
				$d ['ehour'] = $data ['eh'] . ':' . $data ['em'];

				$query = "SELECT roomid
						FROM eventrooms
						WHERE eventdateid = " . $data ['id'];
				$rresult = $db->query ($query);

				$d ['rooms'] = array ();
				while ($rdata = $rresult->fetch_array ())
					$d ['rooms'] [] = $rdata ['roomid'];

				$days [] = $d;
			}
		}
		else {
			$managed = false;
			$total = 0;
			$payed = 0;
			$status = 0;
			$days = $_POST ['days'];
		}

		?>

		<select name="paystatus" class="paystatus span2">
			<option value="0"<?php if ($status == 0) echo ' selected="selected"' ?>>Gratuito</option>
			<option value="1"<?php if ($status != 0) echo ' selected="selected"' ?>>A Pagamento</option>
		</select>

		<div class="paystatus1<?php if ($status == 0) echo " hidden" ?>">
			<hr />

			<form action="" class="form-horizontal">
				<?php

				foreach ($days as $d) {
					list ($end, $half) = explode (':', $d ['ehour']);
					if ($half == '30')
						$end += 0.5;

					list ($start, $half) = explode (':', $d ['shour']);
					if ($half == '30')
						$start += 0.5;

					$hours = $end - $start;

					foreach ($d ['rooms'] as $r) {
						$p = get_room_price ($r);
						$sum = $p * $hours;

						?>

						<div class="control-group">
							<div class="controls">
								<div class="input-append">
									<input disabled="disabled" type="text" class="span1" value="<?php echo $sum ?>" />
									<span class="add-on">€</span>
								</div> +
							</div>
						</div>

						<?php

						if ($managed == false)
							$total += $sum;
					}
				}

				?>

				<hr />

				<div class="control-group">
					<label class="control-label" for="pricetotal">Totale</label>
					<div class="controls">
						<div class="input-append">
							<input name="pricetotal" class="pricetotal span1" type="text" value="<?php echo $total ?>" />
							<span class="add-on">€</span>
						</div>
					</div>
				</div>

				<hr />

				<div class="control-group">
					<label class="control-label" for="pricepayed">Pagato</label>
					<div class="controls">
						<div class="input-append">
							<input name="pricepayed" class="pricepayed span1" type="text" value="<?php echo $payed ?>" />
							<span class="add-on">€</span>
						</div>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="payed">Pagato</label>
					<div class="controls">
						<div class="input-append">
							<input type="checkbox" name="payed" <?php if ($status == 2) echo ' checked="checked"' ?>/>
						</div>
					</div>
				</div>
			</form>
		</div>

		<?php

		break;
}

?>
