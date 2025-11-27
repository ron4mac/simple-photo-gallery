<?php
define('CFGFILE','config.json');

$cfg = json_decode(file_get_contents(CFGFILE),false);

define('PFDW', (int)$cfg->thms->w);
define('PFDH', (int)$cfg->thms->h);

function sendThumb ($fn)
{
	ob_start();
	$fpath = 'media/'.$_GET['f'];
	$idir = dirname($fpath);
	$fnam = '/'.basename($fpath);
	$thmp = $idir.'/.thm';

	// a tad annoying that you can't just use @ in front of mkdir
	$erv = error_reporting();
	error_reporting($erv & ~E_WARNING);
	mkdir($thmp);
	error_reporting($erv);

	$tpth = $thmp . $fnam;
	if (!file_exists($tpth)) {
		require 'classes/imgproc.php';
		$imgp = ImageProc::getImgProc($fpath);
		$sz = $imgp->createThumb($tpth, '', PFDW, PFDH);
		if (!$sz) die('Error when creating a thumbnail');
	}

	if (ob_get_length()) {
		ob_end_clean();
	}

	header('Content-Type: image/jpeg');
	header('Content-Length: ' . filesize($tpth));
	readfile($tpth);
}

sendThumb($_GET['f']);
