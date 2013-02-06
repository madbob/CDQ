<?php

require_once ('login.php');

function get_path_info () {
	return substr ($_SERVER ['SCRIPT_NAME'], 0, strlen ($_SERVER ['SCRIPT_NAME']) - strlen ('printer.php') - 1);
}

global $autokey;
$starting = $_GET ['starting'];

$path = tempnam (sys_get_temp_dir (), 'CDQ_');
$url = 'http://' . $_SERVER ["HTTP_HOST"] . get_path_info ();
exec ("./wkhtmltopdf -B 0 -L 0 -R 0 -T 0 -s A4 --print-media-type --javascript-delay 1000 -d 300 \$url/index.php?starting=$starting&amp;autokey=$autokey\" $path");
$output = file_get_contents ($path);
unlink ($path);

header ('Content-type: application/pdf');
header ('Content-Disposition: attachment; filename="CDQ-' . $starting . '.pdf"');
echo $output;

?>
