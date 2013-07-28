<?php

require_once ('db.php');

$query = "CREATE TABLE config (
		id int auto_increment primary key,
		name varchar(100) default '',
		value varchar(100) default ''
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'config': " . mysql_error ());

$query = "INSERT INTO config (name, value) VALUES ('fontsize', '10')";
mysql_query ($query) or die ("Impossibile popolare tabella 'config': " . mysql_error ());
$query = "INSERT INTO config (name, value) VALUES ('pagesize', '500')";
mysql_query ($query) or die ("Impossibile popolare tabella 'config': " . mysql_error ());


$query = "CREATE TABLE users (
		id int auto_increment primary key,
		username varchar(100) default '',
		password varchar(100) default '',
		permissions int default 0
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'users': " . mysql_error ());


$query = "CREATE TABLE rooms (
		id int auto_increment primary key,
		name varchar(100) default '',
		position int default 0,
		width float default 10,
		visible boolean default true,
		defaultprice decimal(6,2) default 0
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'rooms': " . mysql_error ());


$query = "CREATE TABLE materials (
		id int auto_increment primary key,
		name varchar(100) default ''
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'materials': " . mysql_error ());


$query = "CREATE TABLE contactcategories (
		id int auto_increment primary key,
		name varchar(100) default ''
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'contactcategories': " . mysql_error ());

$query = "INSERT INTO contactcategories (name) VALUES ('Associazione')";
mysql_query ($query) or die ("Impossibile popolare tabella 'contactcategories': " . mysql_error ());
$query = "INSERT INTO contactcategories (name) VALUES ('Privato')";
mysql_query ($query) or die ("Impossibile popolare tabella 'contactcategories': " . mysql_error ());


$query = "CREATE TABLE contacts (
		id int auto_increment primary key,
		name varchar(100) default '',
		surname varchar(100) default '',
		pays boolean default true,
		category int references contactcategories (id),
		mail varchar(100) default '',
		web varchar(100) default '',
		phone varchar(100) default '',
		notes varchar(1000) default ''
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'contacts': " . mysql_error ());


$query = "CREATE TABLE eventcategories (
		id int auto_increment primary key,
		name varchar(100) default ''
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'eventrooms': " . mysql_error ());

$query = "INSERT INTO eventcategories (name) VALUES ('Festa')";
mysql_query ($query) or die ("Impossibile popolare tabella 'eventcategories': " . mysql_error ());
$query = "INSERT INTO eventcategories (name) VALUES ('Riunione')";
mysql_query ($query) or die ("Impossibile popolare tabella 'eventcategories': " . mysql_error ());
$query = "INSERT INTO eventcategories (name) VALUES ('Corso')";
mysql_query ($query) or die ("Impossibile popolare tabella 'eventcategories': " . mysql_error ());
$query = "INSERT INTO eventcategories (name) VALUES ('Conferenza')";
mysql_query ($query) or die ("Impossibile popolare tabella 'eventcategories': " . mysql_error ());


$query = "CREATE TABLE events (
		id int auto_increment primary key,
		type int default 0,
		title varchar(100) default '',
		owner int references contacts (id),
		hasvat boolean default true,
		category int references eventcategories (id),
		public boolean default true,
		price decimal(6,2) default 0,
		partprice decimal(6,2) default 0,
		paystatus int default 0,
		unconfirmed int default 0,
		notes varchar(1000) default ''
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'events': " . mysql_error ());


$query = "CREATE TABLE eventdates (
		id int auto_increment primary key,
		eventid int references rooms (id),
		startdate datetime,
		enddate datetime
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'eventdates': " . mysql_error ());


$query = "CREATE TABLE eventrooms (
		id int auto_increment primary key,
		eventdateid int references eventdates (id),
		roomid int references rooms (id)
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'eventrooms': " . mysql_error ());


$query = "CREATE TABLE eventmaterials (
		id int auto_increment primary key,
		eventdateid int references eventdates (id),
		materialid int references materials (id)
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'eventmaterials': " . mysql_error ());


$query = "CREATE TABLE current_sessions (
		id int auto_increment primary key,
		userid int references users (id),
		init date,
		session_id varchar(100)
	)";
mysql_query ($query) or die ("Impossibile creare tabella 'current_sessions': " . mysql_error ());

?>

