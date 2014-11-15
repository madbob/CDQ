<?php

header ("Content-type: text/css");
require_once ('db.php');
require_once ('utils.php');

?>

* {
	padding: 0px;
	margin: 0px;
}

.login_form {
	margin: auto;
	width: 300px;
}

.login_form fieldset {
	padding: 10px;
}

.login_logo {
	display: block;
	margin: auto;
	padding: 30px;
}

.login_form label {
	width: 100px;
	text-align: right;
	display: inline-block;
}

.login_form .submit {
	text-align: center;
}

.spinner {
	position: fixed;
	margin-left: 40%;
}

.page_table {
	width: 100%;
}

.fixed_header {
	position: fixed;
	top: 0px;
	left: 0;
	right: 0;
	display: block;
	background-color: #FFFFFF;
	z-index: 100;
	width: 100%;
}

.daysep td {
	text-align: center;
	background-color: #FF9900;
}

.datecol {
	text-align: left;
	width: 5.88%;
	background-color: #CCCCFF;
}

.datecol div {
	display: inline-block;
}

.mainhead {
	font-size: <?php echo getconf ('fontsize') ?>px;
	border-spacing: 0px;
	width: 100%;
	border-bottom: 1px solid #CCCCFF;
	background-color: #CCCCFF;
}

.mainhead .datecol div {
	display: inline-block;
}

.mainhead tbody {
	width: 100%;
}

.mainhead tbody tr {
	width: 100%;
}

.mainhead tbody tr td {
	text-align: center;
	font-size: <?php echo getconf ('fontsize') ?>px;
	font-weight: bold;
	padding: 2px;
	border: 1px solid #FFFFFF;
	width: 5.88%;
}

.maintable {
	margin: auto;
	margin-top: 30px;
	font-size: <?php echo getconf ('fontsize') ?>px;
	border-spacing: 0px;
	width: 100%;
}

.maintable tbody {
	width: 100%;
}

.maintable-down {
	margin-top: 210px;
}

.maintable tr {
	width: 100%;
}

.maintable td {
	border: 1px solid #EEEEEE;
	height: <?php echo getconf ('fontsize') ?>px;
	padding: 2px;
	width: 5.88%;
	overflow: hidden;
}

.maintable tr:hover {
	background-color: #EEEEEE;
}

.maintable tr:hover td {
	border: 1px solid #EEEEEE;
}

.maintable tr td:hover {
	border: 1px solid #BBBBBB;
}

.maintable tr td.focus:hover {
	border: 1px solid #000000;
}

.maintable td.focus {
	border: 1px solid #000000;
}

.allocated {
	position: absolute;
	/* border: 1px solid #000000; */
	cursor: pointer;
	cursor: hand;
	font-size: <?php echo getconf ('fontsize') - 2 ?>px;
	height: <?php echo getconf ('fontsize') + 5 ?>px;
	overflow: hidden;
	z-index: 50;
	padding: 2px;
}

.allocated_type_0 {
	background-color: #FDD;
}

.allocated_type_1 {
	background-color: #DFD;
}

.allocated_type_2 {
	background-color: #DDF;
}

.unpayed {
	font-style: italic;
}

.unconfirmed {
	color: #FF0000;
}

.console {
	position: fixed;
	bottom: 5px;
	width: 70%;
	border: 2px solid #000000;
	background-color: #DDDDDD;
	padding: 5px;
	display: block;
	left: 15%;
	text-align: center;
	z-index: 70;
}

.console .nav {
	margin: 0px;
}

.endpage {
	height: 130px;
}

<?php

global $db;

$query = "SELECT * FROM rooms ORDER BY position";
$result = $db->query ($query);

while ($col = $result->fetch_array ()) {
	$rules = array ();
	$rules [] = "width: 5.88%;";

	echo ".head_" . $col ['id'] . " {\n";
	echo join ("\n", $rules);
	echo "}\n\n";

	echo ".cell_" . $col ['id'] . " {\n";
	echo join ("\n", $rules);
	echo "}\n\n";
}

unset ($result);

?>

.configuration {
	z-index: 200;
	display: none;
	position: absolute;
	width: 940px;
}

div.tabscontainer {
	margin: 15px 0px;
}

div.tabscontainer div.tabs {
	list-style: none;
	width: 260px;
	cursor: pointer;
	float:left;
	margin-top: 10px;
	left: 0px;
	z-index: 2;
}

div.tabscontainer div.curvedContainer {
	margin-left: 259px;
	border: 1px solid #7c7c77;
	min-height: 400px;
	-moz-border-radius: 13px;
	border-radius: 13px;
	background-color: #FFFFFF;
}

div.tabscontainer div.wideCurvedContainer {
	border: 1px solid #7c7c77;
	min-height: 400px;
	-moz-border-radius: 13px;
	border-radius: 13px;
	background-color: #FFFFFF;
	font-size:12px;
	height: 100%;
	padding:20px;
}

div.tabscontainer div.curvedContainer .tabcontent{
	display:none;
	padding:20px;
	font-size:12px;
}

div.tabs div.tab {
	display: block;
	height: 58px;
	background: #eeeeea;
	border: #d6d6d2 solid 1px;
	border-top: none;
	position: relative;
	color: #73736b;
}

div.tabs div.link {
	padding-left: 20px;
	padding-top:20px;
	font-size: 14px;
}

div.tabs div.tab.selected {
	color: #ffffff;
	border-right-color: #aeaeaa;
}

div.tabs div.tab.selected {
	background: url(img/menu_bg.png) repeat-x;
	border-right-color: #7c7c77;
}

div.tabs div.tab.first {
	border-top: #dbdbb7 solid 1px;
	-moz-border-radius-topleft: 13px;
	border-top-left-radius: 13px;
}

div.tabs div.tab.last {
	-moz-border-radius-bottomleft: 13px;
	border-bottom-left-radius: 13px;
}

div.tabs div.tab div.arrow {
	position: absolute;
	background: url(img/sel_arrow.png) no-repeat;
	height: 58px;
	width: 17px;
	left: 100%;
	top: 0px;
	display: none;
}

div.tabs div.tab.selected div.arrow {
	display: block;
}

.editevent {
	z-index: 200;
	display: none;
	position: absolute;
}

.form-horizontal .control-label {
	width: 130px;
}

.form-horizontal .controls {
	margin-left: 135px;
}

.borderright {
	padding-right: 20px;
	border-right: 1px solid #DDDDDD;
}

#modal-overlay {
	position: fixed;
	z-index: 100;
	top: 0px;
	left: 0px;
	height: 100%;
	width: 100%;
	background: #000;
	display: none;
}

.hidden {
	display: none;
}

.systemid {
	display: none;
}

.little-text {
	font-size: 10px;
}

.add_button {
	cursor: pointer;
	cursor: hand;
}

li.selected {
	color: #F00;
}

.configuration_users table {
	text-align: center;
}

.contacts_list {
	overflow: auto;
}

.contacts_list ul li {
	list-style: none;
	cursor: pointer;
	cursor: hand;
	width: 90%;
	padding: 2px;
}

.contacts_list ul li:hover {
	background-color: #EEEEEE;
}

.rooms_names_wrapper .add_column {
	margin-top: 15px;
	cursor: hand;
	cursor: pointer;
}

.rooms_names li {
	list-style: none;
	margin-top: 10px;
	cursor: hand;
	cursor: pointer;
}

.rooms_descriptions li {
	display: none;
	list-style: none;
}

.rooms_descriptions li:first-child {
	display: block;
}

.controls span {
	padding-top: 5px;
	display: inline-block;
	font-size: 13px;
	font-weight: normal;
	line-height: 18px;
}

.saving_message {
	width: 70%;
	margin: auto;
	margin-top: 20%;
	padding: 20px;
	text-align: center;
	font-size: 20px;
}

.usersgrid td {
	text-align: center;
}

