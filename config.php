<?php
$cfg = json_decode(file_get_contents(CFGFILE));

if (isset($_POST['gtitle'])) {
	// set the new values
	$cfg->title = $_POST['gtitle'];
	$cfg->desc = $_POST['desc'];
	$cfg->css = $_POST['colrs'];
	$cfg->thms->q = (int)$_POST['tqual'];
	$cfg->thms->w = (int)$_POST['twide'];
	$cfg->thms->h = (int)$_POST['thigh'];
	$rslt = file_put_contents(CFGFILE, json_encode($cfg, JSON_PRETTY_PRINT));
	if ($_POST['thmtok'] != md5(serialize($cfg->thms))) {
		deThumb(IBASE);
	}
	exit(print_r($rslt,true));
}

// send the form body
$html = '<label>Gallery title: <input type="text" name="gtitle" value="'.$cfg->title.'" required autofocus></label>';
$html .= '<label>Gallery description<br><textarea name="desc" rows="3">'.$cfg->desc.'</textarea></label>';
$cs = $cfg->css=='dark' ? [' checked',''] : ['',' checked'];
$html .= '<div>Color scheme: <label><input type="radio" name="colrs" value="dark"'.$cs[0].'> Dark</label> <label><input type="radio" name="colrs" value="lite"'.$cs[1].'> Light</label></div>';
$html .= '<fieldset><legend>&nbsp;Thumbnails&nbsp;</legend>';
$html .= '<label>Quality: <input type="number" name="tqual" min="50" max="100" step="1" value="'.$cfg->thms->q.'"></label>';
$html .= '<br><label>Width: <input type="number" name="twide" min="30" max="400" step="10" value="'.$cfg->thms->w.'"></label>';
$html .= '<br><label>Height: <input type="number" name="thigh" min="30" max="400" step="10" value="'.$cfg->thms->h.'"></label>';
$html .= '</fieldset>';
$html .= '<input type="hidden" name="thmtok" value="'.md5(serialize($cfg->thms)).'">';
$html .= '<input type="hidden" name="cfg" value="1">';

echo $html;
