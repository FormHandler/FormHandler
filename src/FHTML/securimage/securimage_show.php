<?php
include 'securimage.php';
$session_id = (isset($_GET['session_id'])) ? $_GET['session_id'] : false;
$width = (isset($_GET['width']) && (int) $_GET['width'] != 0) ? $_GET['width'] : null;
$height = (isset($_GET['height']) && (int) $_GET['height'] != 0) ? $_GET['height'] : null;
$length = (isset($_GET['length']) && (int) $_GET['length'] != 0) ? $_GET['length'] : null;

$img = new securimage($session_id);
$img->show($width, $height, $length); // alternate use:  $img->show('/path/to/background.jpg');
?>
