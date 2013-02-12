<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="it">

<?php

require_once ('login.php');
require_once ('utils.php');

global $db;
global $current_permissions;
global $minhour;
global $maxhour;

$rooms = retrieve_rooms ();

?>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<title>Calendario del Quartiere</title>

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
	<script type="text/javascript" src="js/jquery.ui.timepicker.js"></script>
	<script type="text/javascript" src="main.js.php"></script>
	<script type="text/javascript" src="js/bootstrap.js"></script>

	<link rel="stylesheet" media="screen" href="css/bootstrap.css" />
	<link rel="stylesheet" media="screen" href="css/jquery-ui.css" />
	<link rel="stylesheet" media="screen" href="css/jquery.ui.timepicker.css" />
	<link rel="stylesheet" media="screen" href="main.css.php" />
	<link rel="stylesheet" media="print" href="print.css.php" />
</head>

<body>

<!------------------------------------------------------------------- HEADER -->

<input type="hidden" name="current_permissions" value="<?php echo $current_permissions ?>" />

<div class="fixed_header">
	<table class="mainhead page_table">
		<tbody>
			<tr>
				<td class="datecol iblock">
					<?php if ($current_permissions == 1): ?>
					<div class="conf_button"><img src="img/conf.png" alt="Configurazioni" /></div> | 
					<?php endif; ?>
					<div class="print_button"><img src="img/printer.png" alt="Stampa" /></div> | <div class="logout_button"><img src="img/logout.png" alt="Logout" /></div>
				</td>

				<?php for ($i = $minhour; $i <= $maxhour; $i++): ?>
				<td class="head_<?php echo $i ?>"><?php echo $i ?>:00</td>
				<?php endfor; ?>
			</tr>
		</tbody>
	</table>
</div>

<!------------------------------------------------------------------ SPINNER -->

<div class="spinner">
	<img src="img/processing.gif" />
</div>

<!------------------------------------------------------- TABELLA PRINCIPALE -->

<table id="maintable" class="maintable page_table">
	<tbody>
		<?php

		$diffh = $maxhour - $minhour + 1;
		$rooms = retrieve_rooms (false);

		for ($i = 0; $i < 7; $i++) {
			?>

			<tr class="daysep">
				<td colspan="<?php echo $diffh + 1 ?>" class="datecol">
					<span></span>
					<input type="hidden" name="date" value="" />
					<input type="hidden" name="weekday" value="<?php echo ($i + 1) ?>" />
				</td>
			</tr>

			<?php foreach ($rooms as $room): ?>

			<tr>

				<td class="datecol">
					<?php echo $room ['name'] ?>
					<input type="hidden" name="roomid" value="<?php echo $room ['id'] ?>" />
				</td>

				<?php for ($a = $minhour; $a <= $maxhour; $a++): ?>
				<td>&nbsp;</td>
				<?php endfor; ?>
			</tr>

			<?php endforeach; ?>

			<?php
		}

		?>

		<tr class="endpage"></tr>
	</tbody>
</table>

<div id="eventspark">
</div>

<!------------------------------------------------------------------- FOOTER -->

<?php

if (array_key_exists ('starting', $_GET) == false) {
	$w = date ('N');

	if ($w != 1)
		$current_week = date ('Y-m-d', strtotime (date ('Y-m-d') . " - " . ($w - 1) . " days"));
	else
		$current_week = date ('Y-m-d');
}
else {
	$current_week = $_GET ['starting'];
}

list ($y, $m, $d) = explode ('-', $current_week);

?>

<div class="console">
	<div>
		<ul class="nav nav-pills navyear">
			<li><a href="#"><?php echo $y - 1 ?></a></li>
			<li class="active"><a href="#" class="<?php echo $y ?>"><?php echo $y ?></a></li>
			<li><a href="#"><?php echo $y + 1 ?></a></li>
		</ul>
		<ul class="nav nav-pills navmonth">
			<?php for ($i = 1; $i <= 12; $i++): ?>
			<li<?php echo ($i == $m ? ' class="active"' : '')?>><a href="#" class="<?php echo $i ?>"><?php echo monthname ($i) ?></a></li>
			<?php endfor; ?>
		</ul>
		<ul class="nav nav-pills pull-right shortnavweek">
			<li><a href="#" class="prevweek">Precedente</a></li>
			<li><a href="#" class="nextweek">Successiva</a></li>
		</ul>
		<ul class="nav nav-pills navweek">
			<?php

			$max = cal_days_in_month (CAL_GREGORIAN, $m, $y);

			$a = 1;
			while (date ('N', strtotime ("$y-$m-$a")) != 1)
				$a++;

			for ($week_start = $a; $week_start <= $max; $week_start += 7)
				echo '<li' . ($week_start >= $d && $week_start < ($d + 7) ? ' class="active"' : '') . '><a href="#" class="<?php echo $week_start ?>">' . $week_start . '</a></li>';

			?>
		</ul>
	</div>
</div>

<?php if ($current_permissions == 1): ?>

<!-------------------------- PANNELLI CONFIGURAZIONI (INIZIALMENTE NASCOSTI) -->

<div class="editevent">
	<input type="hidden" name="eventid" />
	<input type="hidden" name="edittype" />

	<div class="tabscontainer">
		<div class="tabs">
			<div class="tab first selected" id="tab_menu_1">
				<div class="link">1. Chi</div>
				<div class="arrow"></div>
			</div>
			<div class="tab" id="tab_menu_2">
				<div class="link">2. Quando</div>
				<div class="arrow"></div>
			</div>
			<div class="tab last" id="tab_menu_3">
				<div class="link">3. Quanto</div>
				<div class="arrow"></div>
			</div>
		</div>

		<div class="curvedContainer">
			<div style="display: block;" class="tabcontent" id="tab_content_1">
				<div class="row">
					<form action="" method="POST" class="events_parameters form-horizontal">
							<fieldset class="span7 contact_for_event">
								<?php contact_edit_form () ?>
								<a href="#" class="btn pull-right reset_event_contact">Reset</a>
							</fieldset>
					</form>
				</div>

				<hr />

				<div class="row">
					<input type="button" value="Avanti" class="next_button btn btn-primary pull-right" />
				</div>
			</div>

			<div style="display: none;" class="tabcontent" id="tab_content_2">
				<div class="row">
					<form action="" method="POST" class="events_parameters form-horizontal">
						<fieldset class="span7 details_for_event">
							<div class="control-group">
								<label class="control-label" for="title">Titolo</label>
								<div class="controls">
									<input type="text" name="title" />
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="cat">Categoria</label>
								<div class="controls">
									<select name="cat">
										<?php

										$query = "SELECT * FROM eventcategories ORDER BY name";
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
								<label class="control-label" for="private">Evento Privato</label>
								<div class="controls">
									<label class="checkbox">
										<input type="checkbox" name="private" />
									</label>
								</div>
							</div>

							<div class="full">
								<ul class="nav nav-tabs" id="eventtypesel">
									<li class="active"><a href="#eventsingleday">Evento Occasionale</a></li>
									<li><a href="#eventcycle">Evento Ciclico</a></li>
									<li><a href="#eventpermanent">Evento Permanente</a></li>
								</ul>

								<div class="tab-content" id="eventtypeseltabs">
									<div class="tab-pane active" id="eventsingleday">
										<div class="original">
											<div class="replicableday">
												<?php
												day_hidden_id ('');
												day_selector ();
												hour_selector ();
												rooms_selector ();
												materials_selector ();
												?>

												<hr />
											</div>

											<a href="#" class="btn pull-right addday">Aggiungi Giorno</a>
										</div>

										<div class="final">
										</div>
									</div>

									<div class="tab-pane" id="eventcycle">
										<div class="original">
											<?php recurrence_selector (); ?>

											<div class="replicableweekday">
												<hr />

												<?php
												weekday_selector ();
												hour_selector ();
												rooms_selector ();
												materials_selector ();
												?>
											</div>

											<hr />

											<div class="control-group">
												<label class="control-label" for="start">Primo Giorno</label>
												<div class="controls">
													<input type="text" name="start" class="date" />
												</div>
											</div>
											<div class="control-group">
												<label class="control-label" for="repeat">Numero Settimane</label>
												<div class="controls">
													<input type="text" name="repeat" class="numericvalue" />
												</div>
											</div>
											<div class="control-group">
												<label class="control-label" for="verify">Verifica</label>
												<div class="controls">
													<a href="#" class="btn verifydays">Controlla Giorni</a>
												</div>
											</div>
										</div>

										<div class="final">
										</div>
									</div>

									<div class="tab-pane" id="eventpermanent">
										<div class="original">
											<?php recurrence_selector (); ?>

											<div class="replicableweekday">
												<hr />

												<?php
												weekday_selector ();
												hour_selector ();
												rooms_selector ();
												materials_selector ();
												?>
											</div>

											<hr />

											<div class="control-group">
												<label class="control-label" for="verify">Verifica</label>
												<div class="controls">
													<a href="#" class="btn verifydaysyear">Controlla Giorni</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="single">
								<hr />

								<input type="hidden" name="eventtype" />

								<?php
								day_hidden_id ('');
								day_selector ();
								hour_selector ();
								rooms_selector ();
								materials_selector ();
								?>

								<a href="#" class="btn pull-right modevent">Modifica Evento</a>
							</div>
						</fieldset>
					</form>
				</div>

				<hr />

				<div class="row">
					<input type="button" value="Avanti" class="next_button btn btn-primary pull-right" />
					<input type="button" value="Elimina Giorno" class="remove_existing_day hidden btn pull-right" />
					<input type="button" value="Elimina Evento" class="remove_existing_event hidden btn pull-right" />
				</div>
			</div>

			<div style="display: none;" class="tabcontent" id="tab_content_3">
				<form action="" method="POST" class="events_parameters form-horizontal">
					<div class="row">
						<fieldset class="span7 details_for_price">
							<select name="paystatus" class="span2">
								<option value="0">Gratuito</option>
								<option value="1">A Pagamento</option>
							</select>
						</fieldset>
					</div>
				</form>

				<hr />

				<div class="row">
					<input type="submit" value="Salva" class="save_button btn btn-primary pull-right" />
				</div>
			</div>
		</div>
	</div>
</div>

<div class="configuration">
	<div class="tabscontainer">
		<div class="tabs">
			<div class="tab first selected" id="tab_menu_1">
				<div class="link">Contatti</div>
				<div class="arrow"></div>
			</div>
			<div class="tab" id="tab_menu_2">
				<div class="link">Sale</div>
				<div class="arrow"></div>
			</div>
			<div class="tab" id="tab_menu_3">
				<div class="link">Materiali</div>
				<div class="arrow"></div>
			</div>
			<div class="tab" id="tab_menu_4">
				<div class="link">Categorie</div>
				<div class="arrow"></div>
			</div>
			<div class="tab" id="tab_menu_5">
				<div class="link">Configurazioni</div>
				<div class="arrow"></div>
			</div>
			<div class="tab last" id="tab_menu_6">
				<div class="link">Accessi</div>
				<div class="arrow"></div>
			</div>
		</div>

		<div class="curvedContainer">
			<div style="display: block;" class="tabcontent" id="tab_content_1">
				<form action="" method="POST" class="configuration_contacts form-horizontal">
					<div class="row">
						<div class="contacts_list span3">
							<ul>
								<?php

								$query = "SELECT id, name FROM contacts ORDER BY name ASC";
								$results = $db->query ($query);

								while ($c = $results->fetch_array ()) {
									?>

									<li class="contact_<?php echo $c ['id'] ?>"><?php echo $c ['name'] ?></li>

									<?php
								}

								?>
							</ul>
						</div>

						<div class="contact_editable span5">
							<?php contact_edit_form () ?>

							<div class="control-group">
								<label class="control-label" for="remove_<?php echo $id ?>">Rimuovi</label>
								<div class="controls">
									<input type="button" name="remove_<?php echo $id ?>" value="Elimina Contatto" class="remove_contact btn" />
								</div>
							</div>

							<hr />

							<div class="stats">
								<div>
									Dal <input type="text" class="statsstartdate date input-medium" value="<?php echo date ('d/m/Y', strtotime ('-1 year')) ?>">
									Al <input type="text" class="statsenddate date input-medium" value="<?php echo date ('d/m/Y') ?>">
								</div>

								<br />

								<table class="results table">
								</table>
							</div>
						</div>
					</div>

					<input type="submit" value="Salva" class="save_button btn btn-primary pull-right" />
				</form>
			</div>

			<div style="display: none;" class="tabcontent" id="tab_content_2">
				<form action="save_rooms.php" method="POST" class="configuration_rooms form-horizontal">
					<div class="row">
						<div class="rooms_names_wrapper span3">
							<ul class="rooms_names">
								<?php foreach ($rooms as $r): ?>
								<li id="sorting_<?php echo $r ['id'] ?>"><img src="img/sorter.png" class="handle" /> <span><?php echo $r ['name'] ?></span></li>
								<?php endforeach; ?>
							</ul>

							<p class="add_room"><img src="img/add.png" /> Aggiungi Nuovo</p>
						</div>

						<ul class="rooms_descriptions span5">
							<?php
							foreach ($rooms as $r)
								room_properties_form ($r ['id'], $r ['name'], $r ['defaultprice'], $r ['visible']);
							?>
						</ul>
					</div>

					<input type="submit" value="Salva" class="save_button btn btn-primary pull-right" />
				</form>
			</div>

			<div style="display: none;" class="tabcontent" id="tab_content_3">
				<form action="save_materials.php" method="POST" class="configuration_materials form-horizontal">
					<div class="row">
						<div class="mat_names_wrapper span7 offset1">
							<fieldset>
								<?php

								$query = "SELECT * FROM materials ORDER BY name";
								$result = $db->query ($query);

								?>

								<ul class="materials_names">
									<?php while ($r = $result->fetch_array ()): ?>
									<li class="ec_<?php echo $r ['id'] ?>"><input value="<?php echo $r ['name'] ?>" /> <img class="remove_button" src="img/remove.png" /></li>
									<?php endwhile; ?>
								</ul>

								<p class="add_button"><img src="img/add.png" /> Aggiungi Nuovo</p>
							</fieldset>
						</div>
					</div>

					<input type="submit" value="Salva" class="save_button btn btn-primary pull-right" />
				</form>
			</div>

			<div style="display: none;" class="tabcontent" id="tab_content_4">
				<form action="save_categories.php" method="POST" class="configuration_categories form-horizontal">
					<div class="row">
						<div class="cat_names_wrapper span3 offset1">
							<fieldset>
								<?php

								$query = "SELECT * FROM eventcategories ORDER BY name";
								$result = $db->query ($query);

								?>

								<legend>Eventi</legend>
								<br />

								<ul class="event_cat_names">
									<?php while ($r = $result->fetch_array ()): ?>
									<li class="ec_<?php echo $r ['id'] ?>">
										<input value="<?php echo $r ['name'] ?>" class="span2" /> <img class="remove_button" src="img/remove.png" />
									</li>
									<?php endwhile; ?>
								</ul>

								<p class="add_button"><img src="img/add.png" /> Aggiungi Nuovo</p>
							</fieldset>
						</div>

						<div class="cat_names_wrapper span3">
							<fieldset>
								<?php

								$query = "SELECT * FROM contactcategories ORDER BY name";
								$result = $db->query ($query);

								?>

								<legend>Contatti</legend>
								<br />

								<ul class="contacts_cat_names">
									<?php while ($r = $result->fetch_array ()): ?>
									<li class="cc_<?php echo $r ['id'] ?>">
										<input value="<?php echo $r ['name'] ?>" class="span2" /> <img class="remove_button" src="img/remove.png" />
									</li>
									<?php endwhile; ?>
								</ul>

								<p class="add_button"><img src="img/add.png" /> Aggiungi Nuovo</p>
							</fieldset>
						</div>
					</div>

					<input type="submit" value="Salva" class="save_button btn btn-primary pull-right" />
				</form>
			</div>

			<div style="display: none;" class="tabcontent" id="tab_content_5">
				<div class="span7 offset1">
					<form action="save_params.php" method="POST" class="configuration_parameters form-horizontal">
						<fieldset>
							<legend>Amministrazione</legend>

							<div class="control-group">
								<label class="control-label" for="startyear">Inizio Anno Corrente</label>
								<div class="controls">
									<input type="text" class="date" name="startyear" value="<?php echo getconf ('startyear') ?>" />
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="endyear">Fine Anno Corrente</label>
								<div class="controls">
									<input type="text" class="date" name="endyear" value="<?php echo getconf ('endyear') ?>" />
								</div>
							</div>
						</fieldset>

						<fieldset>
							<legend>Interfaccia</legend>

							<div class="control-group">
								<label class="control-label" for="fontsize">Dimensione Caratteri</label>
								<div class="controls">
									<input type="text" class="numericvalue" name="fontsize" value="<?php echo getconf ('fontsize') ?>" /> pixel
								</div>
							</div>
						</fieldset>

						<fieldset>
							<legend>Mail</legend>

							<div class="control-group">
								<label class="control-label" for="mailaddress">Indirizzo Mail</label>
								<div class="controls">
									<input type="text" name="mailaddress" value="<?php echo getconf ('mailaddress') ?>" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" for="smtpserver">Server SMTP</label>
								<div class="controls">
									<input type="text" name="smtpserver" value="<?php echo getconf ('smtpserver') ?>" />:<input type="text" size="6" name="smtpserverport" value="<?php echo getconf ('smtpserverport') ?>" class="span2" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" for="smtpusername">Username</label>
								<div class="controls">
									<input type="text" name="smtpusername" value="<?php echo getconf ('smtpusername') ?>" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" for="smtppassword">Password</label>
								<div class="controls">
									<input type="password" name="smtppassword" value="<?php echo getconf ('smtppassword') ?>" />
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" for="smtpssl">SSL</label>
								<div class="controls">
									<input type="checkbox" name="smtpssl" <?php if (getconf ('smtpssl') == 'true') echo ' checked="checked"' ?> />
								</div>
							</div>
						</fieldset>

						<input type="submit" value="Salva" class="save_button btn btn-primary pull-right" />
					</form>
				</div>
			</div>

			<div style="display: none;" class="tabcontent" id="tab_content_6">
				<div class="users_parameters">
					<p>
						Se lasci il campo "password" vuoto la password non verr&agrave; cambiata (tranne nel caso di un nuovo utente, dove &egrave; obbligatoria).
					</p>

					<hr />

					<table class="usersgrid">
						<thead>
							<tr>
								<th></th>
								<th>Username</th>
								<th>Nuova Password</th>
								<th>Amministratore</th>
								<th></th>
							</tr>
						</thead>

						<tbody>
							<?php

							$query = "SELECT id, username, permissions FROM users";
							$results = $db->query ($query);

							while ($row = $results->fetch_array ()) {
								?>

								<tr>
									<td><input type="hidden" name="userid" value="<?php echo $row ['id'] ?>" /></td>
									<td><input type="text" name="username" value="<?php echo $row ['username'] ?>" /></td>
									<td><input type="password" name="password" value="" /></td>
									<td><input type="checkbox" name="admin"<?php if ($row ['permissions'] == 1) echo ' checked="checked"' ?> /></td>
									<td><input type="button" class="btn remove_user" value="Rimuovi" /></td>
								</tr>

								<?php
							}

							?>

							<tr>
								<td><input type="hidden" name="userid" value="new" /></td>
								<td><input type="text" name="username" value="" /></td>
								<td><input type="password" name="password" value="" /></td>
								<td><input type="checkbox" name="admin" /></td>
								<td><input type="button" class="btn add_user" value="Aggiungi" /></td>
							</tr>
						</tbody>
					</table>

					<hr />

					<input type="button" value="Salva" class="save_button btn btn-primary pull-right" />
				</div>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>

</body>
</html>

