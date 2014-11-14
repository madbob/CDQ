<?php

header ("Content-type: text/css");
require_once ('db.php');
require_once ('utils.php');

$fontsize = getconf ('fontsize') * 0.8;
$margin = 5;
$pagewidth = 297 - ($margin * 2);

?>

* {
	padding: 0px;
	margin: 0px;
}

@page {
	size: auto;
	margin: 5mm;
}

body {
	margin: 0;
	padding: 0;
	width: <?php echo $pagewidth ?>mm;
	height: 210mm;
}

.fixed_header {
	position: fixed;
	top: 0px;
	left: 0;
	right: 0;
	display: block;
	background-color: #FFFFFF;
	z-index: 100;
	width: <?php echo $pagewidth ?>mm;
}

.daysep td {
	text-align: center;
	background-color: #FF9900;
}

.datecol {
	text-align: left;
	width: 13mm;
	background-color: #CCCCFF !important;
}

.datecol div {
	display: inline-block;
}

.mainhead {
	font-size: <?php echo $fontsize ?>px;
	border-spacing: 0px;
	width: <?php echo $pagewidth ?>mm;
	border-bottom: 1px solid #CCCCFF;
	background-color: #CCCCFF;
}

.mainhead .datecol div {
	display: none;
}

.mainhead tbody {
	width: 100%;
}

.mainhead tbody tr {
	width: 100%;
}

.mainhead tbody tr td {
	text-align: center;
	font-size: <?php echo $fontsize ?>px;
	font-weight: bold;
	padding: 2px;
	border: 1px solid #FFFFFF;
	width: 13mm;
}

.maintable {
	margin: auto;
	margin-top: 20px;
	font-size: <?php echo $fontsize ?>px;
	border-spacing: 0px;
	width: <?php echo $pagewidth ?>mm;
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
	overflow: hidden;
	height: <?php echo $fontsize ?>px;
	padding: 2px;
	width: 13mm;
	white-space: nowrap;
}

.allocated {
	position: absolute;
	font-size: <?php echo $fontsize - 2 ?>px;
	height: <?php echo $fontsize ?>px;
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

.console {
	display: none;
}

.endpage {
	display: none;
}

.editevent {
	display: none;
}

.configuration {
	display: none;
}

.hidden {
	display: none;
}
