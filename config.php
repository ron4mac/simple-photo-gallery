<?php
require 'cfgobj.php';

$cfg = $utils->mergeObjectsRecursively($cfgobj, json_decode(file_get_contents(CFGFILE)));

if (isset($_POST['gtitle'])) {
	// set the new values
	$cfg->title = $_POST['gtitle'];
	$cfg->desc = $_POST['desc'];
	$cfg->css = $_POST['colrs'];
	$cfg->imgs->r = isset($_POST['isize']) ? 1 : 0;
	$cfg->imgs->w = (int)$_POST['iwide'];
	$cfg->imgs->h = (int)$_POST['ihigh'];
	$cfg->imgs->q = (int)$_POST['iqual'];
	$cfg->thms->w = (int)$_POST['twide'];
	$cfg->thms->h = (int)$_POST['thigh'];
	$cfg->thms->q = (int)$_POST['tqual'];
	$cfg->ssdly = (int)$_POST['ssdly'];
	$rslt = file_put_contents(CFGFILE, json_encode($cfg, JSON_PRETTY_PRINT));
	if ($_POST['thmtok'] != md5(serialize($cfg->thms))) {
		deThumb(IBASE);
	}
	exit(print_r($rslt,true));
}

if (isset($_POST['bgi'])) {
	// set the new values
	$cfg->bgi = $_POST['bgi'];
	$cfg->pexp = $_POST['pexp'];
	$rslt = file_put_contents(CFGFILE, json_encode($cfg, JSON_PRETTY_PRINT));
	exit(print_r($rslt,true));
}

if ($_POST['cfg']==2) {
	$html = 'This will be advanced.';
	$html .= '<br><label>Background image: <select name="bgi" onchange="showBgi(this)">';
	foreach (glob(__DIR__.'/css/bg_*.jpeg') as $fn) {
		if (preg_match('/bg_(.*)\.jpeg/',$fn,$m)) {
			$spec = $m[1];
			$sel = $spec==$cfg->bgi ? ' selected' : '';
			$html .= '<option value="'.$spec.'"'.$sel.'>'.$spec.'</option>';
		}
	}
	$html .= '</select></label>';
	$html .= '<label>Expand portrait images: <input type="number" name="pexp" min="0" max="100" step="5" value="'.$cfg->pexp.'">%</label>';
	$html .= '<input type="hidden" name="cfg" value="2">';
	exit($html);
}

// send the form body
$html = '<label>Gallery title: <input type="text" name="gtitle" value="'.$cfg->title.'" required autofocus></label>';
$html .= '<label>Gallery description<br><textarea name="desc" rows="3">'.$cfg->desc.'</textarea></label>';
$cs = $cfg->css=='dark' ? [' checked',''] : ['',' checked'];
$html .= '<div>Color scheme: <label><input type="radio" name="colrs" value="dark"'.$cs[0].'> Dark</label> <label><input type="radio" name="colrs" value="lite"'.$cs[1].'> Light</label></div>';
$html .= '<div><label>Slideshow delay (secs): <input type="number" name="ssdly" min="2" max="20" step=".5" value="'.$cfg->ssdly.'"></label></div>';

$html .= '<fieldset><legend>&nbsp;Images&nbsp;</legend>';
$html .= '<label><input type="checkbox" name="isize" '.($cfg->imgs->r?'checked':'').'>Resize</label>';
$html .= '<br><label>Max-Width: <input type="number" name="iwide" min="800" max="6000" step="20" value="'.$cfg->imgs->w.'"></label>';
$html .= '<br><label>Max-Height: <input type="number" name="ihigh" min="800" max="6000" step="20" value="'.$cfg->imgs->h.'"></label>';
$html .= '<br><label>Quality: <input type="number" name="iqual" min="50" max="100" step="1" value="'.$cfg->imgs->q.'"></label>';
$html .= '</fieldset>';

$html .= '<fieldset><legend>&nbsp;Thumbnails&nbsp;</legend>';
$html .= '<label>Width: <input type="number" name="twide" min="30" max="400" step="10" value="'.$cfg->thms->w.'"></label>';
$html .= '<br><label>Height: <input type="number" name="thigh" min="30" max="400" step="10" value="'.$cfg->thms->h.'"></label>';
$html .= '<br><label>Quality: <input type="number" name="tqual" min="50" max="100" step="1" value="'.$cfg->thms->q.'"></label>';
$html .= '</fieldset>';

$html .= '<input type="hidden" name="thmtok" value="'.md5(serialize($cfg->thms)).'">';
$html .= '<input type="hidden" name="cfg" value="1">';

echo $html;
