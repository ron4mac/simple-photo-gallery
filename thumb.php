<?php
define('CFGFILE','config.json');

$cfg = json_decode(file_get_contents(CFGFILE));

define('PFDW', (int)$cfg->thms->w);
define('PFDH', (int)$cfg->thms->h);

function sendThumb ($fn)
{
	ob_start();
	$fpath = 'media/'.$_GET['f'];
	$idir = dirname($fpath);
	$fnam = '/'.basename($fpath);
	$thmp = $idir.'/.thm';
	if (!is_dir($thmp)) mkdir($thmp);
	$tpth = $thmp . $fnam;
	if (!file_exists($tpth)) {
		require 'makthm.php';
		makeThumb($fpath, $tpth);
	}
	if (ob_get_length()) {
		ob_end_clean();
	}
	header('Content-Type: image/jpeg');
	header('Content-Length: ' . filesize($tpth));
	readfile($tpth);
}

sendThumb($_GET['f']);
