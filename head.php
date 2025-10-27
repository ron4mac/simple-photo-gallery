<?php
define('CFGFILE','config.json');
//define('IBASE','media/');

session_name('mmg'.substr(sha1($gbase), -30));
session_start();
$isLogged = isset($_SESSION['logged']);


$cfg = (object) [
	'thms' => (object) ['w'=>240,'h'=>180, 'q'=>90],
	'flds' => (object) ['w'=>128,'h'=>128],
	'css' => 'dark',
	'title' => 'My Media Gallery'
	];
$p2cfgf = (isset($GBADJ)?$GBADJ:'') . CFGFILE;	//path to config file
if (file_exists($p2cfgf)) {
	$cfg = json_decode(file_get_contents($p2cfgf));
} else {
	file_put_contents($p2cfgf, json_encode($cfg, JSON_PRETTY_PRINT));
}
