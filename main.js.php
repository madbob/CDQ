<?php

header("content-type: application/x-javascript");
require_once ('login.php');
require_once ('utils.php');

global $minhour;
global $maxhour;

?>

var currentData = null;

$(document).ready (function () {
	$('.date').datepicker ({
		dateFormat: 'dd/mm/yy'
	});

	$('.hour').timepicker ({
		hourText: 'Ore',
		minuteText: 'Minuti',

		hours: {
			starts: <?php echo $minhour ?>,
			ends: <?php echo $maxhour ?>,
		},
		minutes: {
			starts: 00,
			ends: 30,
			interval: 30
		},

		onSelectCallback: function () {
			recomputeCost ();
		}
	});

	$(".numericvalue").live ('keydown', function (event) {
		if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 13 || event.keyCode == 188 || event.keyCode == 35 || event.keyCode == 36 || event.keyCode == 9) {
		}
		else {
			if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105)) {
				event.preventDefault (); 
			} 
		}
	});

	$('.spinner').hide ();

	$(".tabs .tab[id^=tab_menu]").click (function () {
		var menu = $(this);
		$(".tabs .tab[id^=tab_menu]").removeClass ("selected");
		menu.addClass ("selected");

		var index = menu.attr ("id").split ("tab_menu_") [1];
		$(".curvedContainer .tabcontent").css ("display", "none");
		$(".curvedContainer #tab_content_" + index).css ("display", "block");
	});

	$('.print_button').click (function () {
		s = getCurrentDate ();
		$('body').append ('<iframe src="printer.php?starting=' + s + '"></iframe>');
	});

	/***********************************************************************
		Tabella principale
	*/

	$('.maintable').delegate ('td', 'click', function () {
		show_event_edit ($(this));
	});

	$('.allocated').live ('click', function () {
		show_existing_event_edit ($(this));
	});

	loadCurrentPage ();

	/***********************************************************************
		Pannello evento
	*/

	$('.next_button').click (function () {
		var ind = $(this).parents ('.tabcontent').index ();
		$(this).parents ('.tabscontainer').find ('.tabs .tab:nth-child(' + (ind + 2) + ')').click ();
	});

	$('.reset_event_contact').click (function () {
		reset_event_contact ('contact_for_event');
	});

	$('.contact_for_event input[name=contactname]').autocomplete ({
		source: "fetch_contacts.php",
		minLength: 1,
		select: function (event, ui) {
			$.getJSON ('fetch_contacts.php?action=complete&id=' + ui.item.id, function (data) {
				fillContactForm ('contact_for_event', data);

				$('.details_for_price select[name=paystatus] option[value="' + (data.pays == 0 ? 0 : 1) + '"]').attr ('selected', 'selected');
				$('.details_for_price select[name=paystatus]').change ();
			});
		}
	});

	$('#eventtypesel a').click (function (e) {
		e.preventDefault ();
		$(this).tab ('show');
	});

	$('select[name=room]').live ('change', function () {
		recomputeCost ();
	});

	$('.addroom').live ('click', function () {
		var rs = $(this).parents ('.roomsel');
		var b = rs.find ('.controls:first').clone ();
		b.find ('img').attr ('src', 'img/remove.png').attr ('class', 'removeroom');
		rs.find ('.control-group').append (b);
		recomputeCost ();
		return false;
	});

	$('.removeroom').live ('click', function () {
		$(this).parent ().remove ();
		recomputeCost ();
		return false;
	});

	$('.addday').live ('click', function () {
		addDayBox ($(this).parent ());
		recomputeCost ();
		return false;
	});

	$('.removeday').live ('click', function () {
		$(this).parent ().parent ().parent ().remove ();
		recomputeCost ();
		return false;
	});

	$('#eventcycle .iterations button').click (function () {
		var n = parseInt ($(this).text ());
		var l = $('#eventcycle .replicableweekday').length;

		if (n < l) {
			for (i = l; i > n; i--)
				$('#eventcycle .replicableweekday:last').remove ();
		}
		else {
			for (i = l; i < n; i++) {
				var d = $('#eventcycle .replicableweekday:first').clone (false);
				adjustPickers (d);
				$('#eventcycle .replicableweekday:last').after (d);
			}
		}
	});

	$('.monthpos button').click (function () {
		if ($(this).index () == $(this).parent ().children ().length - 1) {
			return;
		}
		else {
			m = $(this).parent ().parent ().parent ().parent ();
			m.find ('.weekpos .active').removeClass ('active');
			m.find ('.weekpos a:last').addClass ('active');
		}
	});

	$('.weekpos button').click (function () {
		if ($(this).index () == $(this).parent ().children ().length - 1) {
			return;
		}
		else {
			m = $(this).parent ().parent ().parent ().parent ();
			m.find ('.monthpos .active').removeClass ('active');
			m.find ('.monthpos a:last').addClass ('active');
		}
	});

	$('.verifydays').click (function () {
		repeats = $('#eventcycle input[name=repeat]').val ();
		if (repeats == '') {
			$('#eventcycle input[name=repeat]').parent ().parent ().addClass ('error');
			return false;
		}
		else {
			$('#eventcycle input[name=repeat]').parent ().parent ().removeClass ('error');
		}

		start = $('#eventcycle input[name=start]').val ();
		onmonth = $('#eventcycle .monthpos .active').index ();
		onweek = $('#eventcycle .weekpos .active').index ();

		weekdays = new Array ();
		$('#eventcycle .replicableweekday').each (function () {
			var rooms = new Array ();
			$(this).find ('.roomsel select[name=room] option:selected').each (function () {
				rooms.push ($(this).val ());
			});

			var materials = new Array ();
			$(this).find ('.materialsel select[name=material] option:selected').each (function () {
				materials.push ($(this).val ());
			});

			weekdays.push ($(this).find ('select[name=weekday]').val () + "|" + $(this).find ('input[name=shour]').val () + "|" + $(this).find ('input[name=ehour]').val () + "|" + rooms.join (',') + "|" + materials.join (','));
		});

		$.get ('async_ui.php?type=check_days&start=' + start + '&weekdays=' + weekdays.join (';') + '&repeats=' + repeats + '&onmonth=' + onmonth + '&onweek=' + onweek, function (data) {
			$('#eventcycle .original').hide ();
			$('#eventcycle .final').empty ().show ().append (data);
			recomputeCost ();
		});

		return false;
	});

	$('.verifydaysyear').click (function () {
		repeats = -1;
		onmonth = $('#eventpermanent .monthpos .active').index ();
		onweek = $('#eventpermanent .weekpos .active').index ();

		weekdays = new Array ();
		$('#eventpermanent .replicableweekday').each (function () {
			var rooms = new Array ();
			$(this).find ('.roomsel select[name=room] option:selected').each (function () {
				rooms.push ($(this).val ());
			});

			var materials = new Array ();
			$(this).find ('.materialsel select[name=material] option:selected').each (function () {
				materials.push ($(this).val ());
			});

			weekdays.push ($(this).find ('select[name=weekday]').val () + "|" + $(this).find ('input[name=shour]').val () + "|" + $(this).find ('input[name=ehour]').val () + "|" + rooms.join (',') + "|" + materials.join (','));
		});

		$.get ('async_ui.php?type=check_days&start=all&weekdays=' + weekdays.join (';') + '&repeats=' + repeats + '&onmonth=' + onmonth + '&onweek=' + onweek, function (data) {
			$('#eventpermanent .original').hide ();
			$('#eventpermanent .final').empty ().show ().append (data);
			recomputeCost ();
		});

		return false;
	});

	$('.remove_existing_day').click (function () {
		var dayid = $('.details_for_event .single input[name=dayid]').val ();

		$.getJSON ('fetch_event.php?action=removeday&id=' + dayid, function (data) {
			closeDialog ($(".editevent"));
			loadCurrentPage ();
		});
	});

	$('.remove_existing_event').click (function () {
		var eventid = $('.editevent input[name=eventid]').val ();

		$.getJSON ('fetch_event.php?action=removeevent&id=' + eventid, function (data) {
			closeDialog ($(".editevent"));
			loadCurrentPage ();
		});
	});

	$('.back_cycle').live ('click', function () {
		$(this).parents ('.tab-pane').find ('.original').show ();
		$(this).parents ('.tab-pane').find ('.final').empty ().hide ();
	});

	$('.editevent .save_button').click (function () {
		c = eventContactJSON ('contact_for_event');
		if (c.name == '') {
			alert ('Non hai assegnato questo evento a nessun contatto (vecchio o nuovo)');
			return false;
		}

		title = $('.details_for_event input[name=title]').val ();
		if (title == '') {
			alert ('Non hai assegnato alcun titolo a questo evento');
			return false;
		}

		id = $('.editevent input[name=eventid]').val ();

		if ($('.details_for_price select[name=paystatus] option:selected').val () == 0)
			paystatus = 0;
		else if ($('.details_for_price input[name=payed]').is ('checked'))
			paystatus = 2;
		else
			paystatus = 1;

		if (id == 'new') {
			type = $('#eventtypeseltabs div[class~=tab-pane][class~=active]').index ();
			days = newEventDays ();

			$.post ('save_event.php', {
				contact: c,
				id: 'new',
				title: title,
				private_event: $('.details_for_event input[name=private]').is ('checked') ? 'true' : 'false',
				category: $('.details_for_event select[name=cat] option:selected').val (),
				price: $('.details_for_price input[name=pricetotal]').val (),
				partprice: $('.details_for_price input[name=pricepayed]').val (),
				paystatus: paystatus,
				type: type,
				days: days,
				changetype: 'full'
			}, function () {
				closeDialog ($(".editevent"));
				loadCurrentPage ();
			});
		}
		else {
			edittype = $('.editevent input[name=edittype]').val ();
			if (edittype == 'day')
				node = $('.single');
			else
				node = $('.full');

			days.push (eventDayAttributes (node));

			$.post ('save_event.php', {
				contact: c,
				id: id,
				title: title,
				private_event: $('.details_for_event input[name=private]').is ('checked') ? 'true' : 'false',
				category: $('.details_for_event select[name=cat] option:selected').val (),
				price: $('.details_for_price input[name=pricetotal]').val (),
				partprice: $('.details_for_price input[name=pricepayed]').val (),
				paystatus: paystatus,
				days: days,
				changetype: edittype
			}, function () {
				closeDialog ($(".editevent"));
				loadCurrentPage ();
			});
		}

		return false;
	});

	$('.modevent').click (function () {
		$.get ('async_ui.php?type=event_days&id=' + $('.editevent input[name=eventid]').val (), function (data) {
			$('.editevent .full').show ();
			$('.editevent .single').hide ();
			$('.editevent input[name=edittype]').val ('full');

			$('.editevent .remove_existing_day').hide ();
			$('.editevent .remove_existing_event').show ();

			/*
				Si suppone che i dati del contatto e dell'evento
				generico siano invariati
			*/

			type = parseInt ($('.details_for_event input[name=eventtype]').val ());

			switch (type) {
				case 0:
					target = '#eventsingleday';
					break;

				case 1:
					target = '#eventcycle';
					break;

				case 2:
					break;
			}

			$('#eventtypesel li:nth-child(' + (type + 1) + ') a').click ();
			$(target + ' .original').hide ();
			$(target + ' .final').empty ().show ().append (data);
			adjustPickers ($(target + ' .final'));
		});

		return false;
	});

	$('select[name=paystatus]').live ('change', function () {
		if ($(this).find ('option:selected').val () == 0)
			$(this).siblings ('.paystatus1').hide ();
		else
			$(this).siblings ('.paystatus1').show ();
	});

	/***********************************************************************
		Pannello configurazioni
	*/

	$('.contacts_list li').live ('click', function () {
		toks = $(this).attr ('class').split ('_');
		$.getJSON ('fetch_contacts.php?action=complete&id=' + toks [1], function (data) {
			fillContactForm ('contact_editable', data);
		});
	});

	$('.remove_contact').click (function () {
		if (confirm ('Sei sicuro di voler eliminare questo contatto? Tutti gli eventi correlati saranno eliminati') == true) {
			id = $('.contact_editable input[name=contactid]').val ();

			$.post ('fetch_contacts.php?action=remove&id=' + id, function () {
				$('.contacts_list li.contact_' + id).remove ();
				reset_event_contact ('contact_editable');
			});
		}

		return false;
	});

	$('.configuration_contacts .save_button').click (function () {
		c = eventContactJSON ('contact_editable');
		if (c.name == '') {
			alert ('Non hai specificato nessun nome');
			return false;
		}

		$.post ('fetch_contacts.php?action=save', {contact: c}, function (data) {
			if (data != '-1')
				$('.contact_editable input[name=contactid]').val (data);
			closeDialog ($(".configuration"));
		});

		return false;
	});

	$(".rooms_names_wrapper .add_room").click (function () {
		var id = 0;

		do {
			id = Math.floor (Math.random () * 1000);
		} while ($(".rooms_descriptions #properties_" + id).length != 0);

		$.get ('async_ui.php?type=room_form&id=' + id, function (data) {
			$(".rooms_descriptions").append (data);
			$(".rooms_descriptions #properties_" + id).css ("display", "block");
		});

		$(".rooms_names").append ('<li id="sorting_' + id + '"><img src="img/sorter.png" class="handle" /> <span>Anonimo</span></li>');
		$(".rooms_descriptions li").css ("display", "none");

		return false;
	});

	$(".rooms_names li").live ('click', function () {
		var item = $(this);
		$(".rooms_names li").removeClass ("selected");
		item.addClass ("selected");

		var index = item.attr ("id").split ("sorting_") [1];
		$(".rooms_descriptions li").css ("display", "none");
		$(".rooms_descriptions #properties_" + index).css ("display", "block");

		return false;
	});

	$(".rooms_descriptions .name_value").live ('keyup', function (event) {
		var index = $(this).parent ().parent ().parent ().attr ("id").split ("properties_") [1];
		$(".rooms_names #sorting_" + index + " span").html ($(this).val ());
	});

	$(".rooms_descriptions .remove_room").live ('click', function () {
		var block = $(this).parents ('li');
		var id = block.attr ("id").split ("properties_") [1];

		if (block.find ('input[name=old_element]').length == 0) {
			$(".rooms_names #sorting_" + id).remove ();
			block.remove ();
		}
		else {
			if (confirm ("Sei sicuro?\nTutti i dati relativi a questa sala verranno eliminati!") == true) {
				$.post ('save_rooms.php?action=remove', {id: id}, function () {
					$(".rooms_names #sorting_" + id).remove ();
					block.remove ();
				});
			}
		}

		return false;
	});

	$(".configuration_rooms .rooms_names").sortable ({
		handle: '.handle',
		update: function () {
			$.post ('save_rooms.php?action=sorting', $('.configuration_rooms .rooms_names').sortable('serialize'));
		}
	});

	$('.add_button').click (function () {
		$(this).siblings ('ul').append ('<li class="new"><input value="" class="span2" /> <img class="remove_button" src="img/remove.png" /></li>');
	});

	$('.remove_button').live ('click', function () {
		$(this).parent ().remove ();
	});

	$('.configuration_categories').submit (function () {
		var events = new Array ();

		$(this).find ('.event_cat_names li').each (function () {
			s = $(this).attr ('class') + '=' + $(this).find ('input').val ();
			events.push (s);
		});

		var contacts = new Array ();

		$(this).find ('.contacts_cat_names li').each (function () {
			s = $(this).attr ('class') + '=' + $(this).find ('input').val ();
			contacts.push (s);
		});

		$(this).append ('<input type="hidden" name="events" value="' + events.join ('###') + '" />');
		$(this).append ('<input type="hidden" name="contacts" value="' + contacts.join ('###') + '" />');
		return true;
	});

	$('.configuration_materials').submit (function () {
		var materials = new Array ();

		$(this).find ('.materials_names li').each (function () {
			s = $(this).attr ('class') + '=' + $(this).find ('input').val ();
			materials.push (s);
		});

		$(this).append ('<input type="hidden" name="materials" value="' + materials.join ('###') + '" />');
		return true;
	});

	$('.users_parameters .remove_user').live ('click', function () {
		$(this).parent ().parent ().remove ();
	});

	$('.users_parameters .add_user').click (function () {
		row = $(this).parent ().parent ();
		newrow = row.clone ();
		row.before (newrow);

		row.find ('input[type=text]').val ('');
		row.find ('input[type=password]').val ('');
		row.find ('input[type=checkbox]').removeAttr ('checked');

		newrow.find ('.add_user').removeClass ('add_user').addClass ('remove_user').val ('Rimuovi');
	});

	$('.users_parameters .save_button').click (function () {
		var users = new Array ();

		$('.usersgrid tbody tr').each (function () {
			user = {
				id: $(this).find ('input[name=userid]').val (),
				username: $(this).find ('input[name=username]').val (),
				password: $(this).find ('input[name=password]').val (),
				permission: $(this).find ('input[name=admin]').is ('checked') ? '1' : '0'
			};

			users.push (user);
		});

		$.post ('save_users.php', JSON.stringify (users), function () {
			closeDialog ($(".configuration"));
		});

		return false;
	});

	/***********************************************************************
		Footer
	*/

	$(".conf_button img").click (function () {
		syncContents ();
		showDialog ($(".configuration"));
	});

	$(".logout_button img").click (function () {
		$.get ('login.php?action=logout', function (data) {
			window.location.reload (true);
		});
	});

	$('.console .navyear li').live ('click', function () {
		onNavClick ($(this), true);
	});

	$('.console .navmonth li').live ('click', function () {
		onNavClick ($(this), true);
	});

	$('.console .navweek li').live ('click', function () {
		onNavClick ($(this), false);
	});

	$('.console .shortnavweek .prevweek').live ('click', function () {
		if ($('.console .navweek .active').index () == 0) {
			if ($('.console .navmonth .active').index () == 0) {
				setNav ($('.console .navyear .active').prev (), true);
				setNav ($('.console .navmonth li:last'), true);
				onNavClick ($('.console .navweek li:last'), false);
			}
			else {
				setNav ($('.console .navmonth .active').prev (), true);
				onNavClick ($('.console .navweek li:last'), false);
			}
		}
		else {
			onNavClick ($('.console .navweek .active').prev (), false);
		}
	});

	$('.console .shortnavweek .nextweek').live ('click', function () {
		if ($('.console .navweek .active').index () == $('.console .navweek li').length - 1) {
			if ($('.console .navmonth .active').index () == $('.console .navmonth li').length - 1) {
				setNav ($('.console .navyear .active').next (), true);
				setNav ($('.console .navmonth li:first'), true);
				onNavClick ($('.console .navweek li:first'), false);
			}
			else {
				setNav ($('.console .navmonth .active').next (), true);
				onNavClick ($('.console .navweek li:first'), false);
			}
		}
		else {
			onNavClick ($('.console .navweek .active').next (), false);
		}
	});
});

function addDayBox (node) {
	var box = node.find ('.replicableday').clone (false);
	adjustPickers (box);
	box.removeClass ('replicableday');
	box.addClass ('dupreplicableday');
	box.find ('input[name=start]').val ('').after ('&nbsp;<img src="img/remove.png" class="removeday" />');
	node.find ('.addday').before (box);
	return box;
}

function setupDayBox (node, info) {
	node.find ('input[name=dayid]').val (info.dayid);
	node.find ('input[name=start]').val (info.start);
	node.find ('input[name=shour]').val (info.shour);
	node.find ('input[name=ehour]').val (info.ehour);
	select_rooms (node.find ('.roomsel'), info.rooms);
}

function eventDayAttributes (node) {
	rooms = new Array ();

	node.find ('.roomsel select[name=room] option:selected').each (function () {
		rooms.push ($(this).val ());
	});

	materials = new Array ();

	node.find ('.materialsel select[name=material] option:selected').each (function () {
		materials.push ($(this).val ());
	});

	d = {
		dayid: node.find ('input[name=dayid]').val (),
		start: node.find ('input[name=start]').val (),
		shour: node.find ('input[name=shour]').val (),
		ehour: node.find ('input[name=ehour]').val (),
		rooms: rooms,
		materials: materials
	};

	return d;
}

function newEventDays () {
	days = new Array ();
	type = $('#eventtypeseltabs div[class~=tab-pane][class~=active]').index ();

	switch (type) {
		case 0:
			$('#eventsingleday .replicableday, #eventsingleday .dupreplicableday').each (function () {
				days.push (eventDayAttributes ($(this)));
			});
			break;

		case 1:
			$('#eventcycle .final .replicableday, #eventcycle .final .dupreplicableday').each (function () {
				days.push (eventDayAttributes ($(this)));
			});
			break;

		case 2:
			/*
				TODO
			*/
			break;
	}

	return days;
}

function showDialog (target) {
	target.css ("top", (($(window).height () - target.outerHeight()) / 2) + $(window).scrollTop () + "px");
	target.css ("left", (($(window).width () - target.outerWidth()) / 2) + $(window).scrollLeft () + "px");

	var overlay = $("<div id='modal-overlay'></div>");
	$("body").append (overlay.click (function () {
		closeDialog (target);
	}));
	overlay.css ("opacity", 0.8);
	overlay.fadeIn (150);

	target.show ();
}

function closeDialog (target) {
	target.hide ();

	var overlay = $('#modal-overlay');
	overlay.fadeOut (function () { 
		$(this).remove (); 
	});
}

function reset_event_contact (parentclass) {
	$('.' + parentclass + ' input').val ('');
	$('.' + parentclass + ' input[type=checkbox]').removeAttr ('checked');
	$('.' + parentclass + ' input[name=contactid]').val ('new');
}

function reset_event_edit () {
	reset_event_contact ('contact_for_event');

	$('.editevent .tabs .tab:nth-child(1)').click ();
	$('.editevent input[name=eventid]').val ('new');
	$('.editevent input[name=private]').removeAttr ('checked');
	$('.editevent input[type=text]').val ('');
	$('.editevent .dupreplicableday').remove ();
	$('.editevent #eventtypesel a:first').tab ('show');
	$('.editevent #eventsingleday .final').empty ().hide ();
	$('.editevent #eventsingleday .original').show ();
	$('.editevent #eventcycle .final').empty ().hide ();
	$('.editevent #eventcycle .original').show ();

	$('.editevent .roomsel').each (function () {
		tot = $(this).find ('select[name=room]').length;
		for (i = 1; i < tot; i++)
			$(this).find ('.controls:last').remove ();
	});
}

function show_event_edit (node) {
	reset_event_edit ();
	$('.editevent .full').show ();
	$('.editevent .single').hide ();

	var i = node.index ();

	if ($(this).hasClass ('daysep')) {
		$('.editevent input[name=shour]').val ('<?php echo $minhour ?>:00');
		$('.editevent input[name=ehour]').val ('<?php echo $maxhour ?>:00');

		/* TODO

		var rs = $(this).parents ('.roomsel');
		var b = rs.find ('.controls:first').clone ();
		b.find ('img').attr ('src', 'img/remove.png').attr ('class', 'removeroom');
		rs.find ('.control-group').append (b);

		*/
	}
	else {
		if (i == 0) {
			$('.editevent input[name=shour]').val ('<?php echo $minhour ?>:00');
			$('.editevent input[name=ehour]').val ('<?php echo $maxhour ?>:00');
		}
		else {
			$('.editevent input[name=shour]').val ((<?php echo $minhour ?> + i - 1) + ':00');
			$('.editevent input[name=ehour]').val ((<?php echo $minhour ?> + i) + ':00');
		}

		var r = node.siblings ('[class=datecol]').find ('input[name=roomid]').val ();
		$('.editevent select[name=room] option[value=' + r + ']').attr ('selected', 'selected');
	}

	dayhead = node.parent ().prevUntil ('tr [class=datesep]');
	$('.editevent input[name=start]').val (dayhead.find ('input[name=date]').val ());
	$('.editevent select[name=weekday]').val (dayhead.find ('input[name=weekday]').val ());

	$('.editevent .remove_existing_day').hide ();
	$('.editevent .remove_existing_event').hide ();

	recomputeCost ();
	showDialog ($(".editevent"));
}

function show_existing_event_edit (node) {
	reset_event_edit ();

	$.getJSON ('fetch_event.php?action=day&id=' + node.find ('input[name=eventid]').val (), function (data) {
		$('.editevent .full').hide ();
		$('.editevent .single').show ();
		$('.editevent input[name=edittype]').val ('day');

		$('.editevent .remove_existing_day').show ();
		$('.editevent .remove_existing_event').hide ();

		fillContactForm ('contact_for_event', data.contact);

		$('.editevent input[name=eventid]').val (data.id);
		$('.details_for_event input[name=eventtype]').val (data.type);
		$('.details_for_event input[name=title]').val (data.title);
		$('.details_for_event select[name=category] option[value=' + data.category + ']').attr ('selected', 'selected');
		if (data.private_event == true)
			$('.details_for_event input[name=private]').attr ('checked', 'checked');
		else
			$('.details_for_event input[name=private]').removeAttr ('checked');

		setupDayBox ($('.single'), data);
		recomputeCost ();
		showDialog ($(".editevent"));
	});
}

function select_rooms (target, rooms) {
	r = rooms [0];
	s = target.find ('select[name=room]');
	s.find ('option[value=' + r + ']').attr ('selected', 'selected');
	s = s.parent ();

	if (rooms.length > 1) {
		for (i = 1; i < rooms.length; i++) {
			r = rooms [i];
			ns = $('<?php single_room_selector (-1, null, true, false); ?>');
			ns.find ('option[value=' + r + ']').attr ('selected', 'selected');
			s.after (ns);
			s = ns;
		}
	}
}

function adjustPickers (box) {
	var d = box.find ('input[class~="date"]').removeClass ('hasDatepicker');
	d.datepicker ({
		dateFormat: 'dd/mm/yyyy'
	});

	var t = box.find ('input[class~="hour"]').removeClass ('hasTimepicker');
	t.timepicker ({
		hourText: 'Ore',
		minuteText: 'Minuti',

		hours: {
			starts: <?php echo $minhour ?>,
			ends: <?php echo $maxhour ?>,
		},
		minutes: {
			starts: 00,
			ends: 30,
			interval: 30
		}
	});
}

function eventContactJSON (parentclass) {
	return {
		id: $('.' + parentclass + ' input[name=contactid]').val (),
		name: $('.' + parentclass + ' input[name=contactname]').val (),
		category: $('.' + parentclass + ' select[name=contactcat] option:selected').val (),
		mail: $('.' + parentclass + ' input[name=contactmail]').val (),
		phone: $('.' + parentclass + ' input[name=contactphone]').val (),
		web: $('.' + parentclass + ' input[name=contactweb]').val (),
		notes: $('.' + parentclass + ' textarea[name=contactnotes]').val ()
	};
}

function setNav (node, alter_weeks) {
	node.siblings ().removeClass ('active');
	node.addClass ('active');

	if (alter_weeks == true) {
		y = $('.navyear .active').text ();
		m = $('.navmonth .active a').attr ('class');

		ex_week = $('.navweek .active').index () + 1;
		$('.navweek').empty ();

		/* inspired by http://www.dzone.com/snippets/determining-number-days-month */
		max = 32 - new Date (y, m, 32).getDate ();

		week_start = 0;

		do {
			week_start++;
			tmp = new Date ();
			tmp.setFullYear (y, m - 1, week_start);
			w = tmp.getDay ();
		} while (w != 1);

		rows = 0;

		for (; week_start <= max; week_start += 7) {
			$('.navweek').append ('<li><a href="#" class="' + week_start + '">' + week_start + '</a></li>');
			rows++;
		}

		if (rows < ex_week)
			ex_week = rows;

		$('.navweek li:nth-child(' + ex_week + ')').addClass ('active');
	}
}

function setupHover () {
	$('.maintable td').hover (function () {
		$('.mainhead td').css ('background-color', '');

		var i = $(this).index () + 1;
		if (i < 2)
			return;

		for (a = 0; a < i; a++) {
			c = $(this).parent ().find ('td:nth-child(' + a + ')').attr ('colspan');
			if (typeof c != 'undefined')
				i += parseInt (c - 1);
		}

		if (typeof $(this).attr ('colspan') != 'undefined') {
			for (e = 0; e < parseInt ($(this).attr ('colspan')); e++)
				$('.mainhead td:nth-child(' + (i + e) + ')').css ('background-color', '#EEEEEE');
		}
		else {
			$('.mainhead td:nth-child(' + i + ')').css ('background-color', '#EEEEEE');
		}

		$(this).parent ().parent ().find ('tr td:nth-child(1)').css ('background-color', '');
		$(this).parent ().find ('td:nth-child(1)').css ('background-color', '#EEEEEE');
	});
}

function loadCurrentData () {
	var data = currentData;
	$('.allocated').remove ();

	for (i = 0; i < 7; i++) {
		d = data.weekdays [i];
		row = $('.maintable .daysep:eq(' + i + ')');
		row.find ('span').text (d.name);
		row.find ('input[name=date]').val (d.date);
	}

	setupHover ();

	for (a = 0; a < data.events.length; a++) {
		ev = data.events [a];

		stokens = ev.shour.split (':');
		shour = parseInt (stokens [0]);
		if (stokens [1] != '00')
			shour += 0.5;

		etokens = ev.ehour.split (':');

		ehour = parseInt (etokens [0]);
		if (etokens [1] != '00')
			ehour += 0.5;

		/*
			Qui (e sotto) sfrutto il fatto che wkhtmltopdf,
			usato per produrre i PDF delle pagine, non
			implementa la funzione window.matchMedia().
			Percui posso calcolare le dimensioni o
			dinamicamente prendendo i riferimenti sullo
			schermo, o basandomi su dimensioni fisse e note
			definite nel CSS per i fogli A4
		*/
		if (window.matchMedia) {
			left = $('.mainhead td.head_' + stokens[0]);
			leftpx = left.offset ().left;

			if (stokens [1] != '00')
				leftpx = leftpx + (left.width () / 2);

			leftpx = leftpx + 'px';

			width = left.width () * (ehour - shour);
			width = width + 'px';
		}
		else {
			/*
				larghezza del foglio A4 standard   /
				numero di colonne                  =
				larghezza fissa di ogni colonna
			*/
			standard = 210 / (<?php echo ($maxhour - $minhour) + 2 ?>);
			width = standard;
			width = width + 'mm';
			leftpx = standard * ((shour - <?php echo $minhour ?>) + 1);
			leftpx = leftpx + 'mm';
		}

		typeclass = 'allocated_type_' + ev.type;

		for (i = 0; i < ev.rooms.length; i++) {
			row = $('.daysep:has(input[value="' + ev.day + '"])').nextAll ('tr:has(td.datecol input[name=roomid][value=' + ev.rooms [i] + ']):first');

			if (window.matchMedia) {
				t = row.offset ().top;
			}
			else {
				/*
					20 e' il margine alto della tabella principale
					3 dovrebbe rappresentare la somma dei margini per ogni cella
					1.2 e' totalmente arbitrario ed empirico
				*/
				t = ((row.index ()) * <?php echo getconf ('fontsize') * 1.2 ?>) + ((row.index ()) * 3) + 20;
			}

			box = $('<div class="allocated ' + typeclass + '">' + ev.name + '<input type="hidden" name="eventid" value="' + ev.id + '" /></div>');
			$('#eventspark').append (box);

			box.css ('left', leftpx);
			box.css ('width', width);
			box.css ('top', t + 'px');
		}
	}
}

function getCurrentDate () {
	y = $('.navyear .active').text ();
	m = $('.navmonth .active a').attr ('class');
	d = $('.navweek .active').text ();
	s = y + "-" + m + "-" + d;
	return s;
}

function loadCurrentPage () {
	s = getCurrentDate ();
	loading (true);

	$.getJSON ('async_ui.php?type=page&start=' + s, function (data) {
		currentData = data;
		loadCurrentData ();
		loading (false);
	});

	return false;
}

function syncContents () {
	having = new Array ();
	$('.contacts_list ul li').each (function () {
		toks = $(this).attr ('class').split ('_');
		id = toks [1];
		having.push (id);
	});

	$.getJSON ('fetch_contacts.php?action=list&having=' + having.join ('::'), function (data) {
		for (i = 0; i < data.length; i++) {
			d = data [i];
			found = false;

			$('.contacts_list ul li').each (function () {
				if ($(this).text () > d.name) {
					$(this).before ('<li class="contact_' + c.id + '">' + c.name + '</li>');
					found = true;
					return false;
				}
			});

			if (found == true)
				$('.contacts_list ul').append ('<li class="contact_' + c.id + '">' + c.name + '</li>');
		}
	});	
}

function fillContactForm (parentclass, data) {
	$('.' + parentclass + ' input[name=contactid]').val (data.id);
	$('.' + parentclass + ' input[name=contactname]').val (data.name);
	$('.' + parentclass + ' select[name=contactcat] option[value=' + data.category + ']').attr ('selected', 'selected');
	$('.' + parentclass + ' input[name=contactphone]').val (data.phone);
	$('.' + parentclass + ' input[name=contactmail]').val (data.mail);
	$('.' + parentclass + ' input[name=contactweb]').val (data.web);
	$('.' + parentclass + ' textarea[name=contactnotes]').val (data.notes);
}

function onNavClick (node, alter_weeks) {
	setNav (node, alter_weeks);
	loadCurrentPage ();
}

function recomputeCost () {
	id = $('.editevent input[name=eventid]').val ();
	if (id == 'new')
		days = newEventDays ();
	else
		days = new Array ();

	status = $('.details_for_price select[name=paystatus] option:selected').val ();

	$('.details_for_price > select').remove ();
	$('.details_for_price .paystatus1').remove ();

	$.get ('async_ui.php?type=check_payment', {days: days, id: id, status: status}, function (data) {
		$('.details_for_price').append (data);
	});
}

function loading (show) {
	if (show == true)
		$('.spinner').show ();
	else
		$('.spinner').hide ();
}
