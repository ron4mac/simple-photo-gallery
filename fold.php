<?php
$foldattrs = [
	'desc' => '',
	'pubup' => 0,
	'picf' => 0
];

$foldp = $_POST['fld'];
$foldf = IBASE . $foldp . '.fold';
if (file_exists($foldf)) {
	$attrs = json_decode(file_get_contents($foldf), true);
	foreach($attrs as $k=>$v) {
		$foldattrs[$k] = $v;
	}
}
if (isset($_POST['fldsv'])) {
	// set the new values
	$foldattrs['desc'] = $_POST['desc'];
	$foldattrs['pubup'] = empty($_POST['pubup']) ? 0 : 1;
	$foldattrs['picf'] = empty($_POST['picf']) ? 0 : 1;
	file_put_contents($foldf, json_encode($foldattrs, JSON_PRETTY_PRINT));
	exit();
}

$chkv = ['',' checked'];
// send the form body
$html = '<label>Folder description<br><textarea name="desc" rows="3">'.$foldattrs['desc'].'</textarea></label>';
$html .= '<label><input type="checkbox" name="pubup" '.$chkv[$foldattrs['pubup']].'> Can receive public upload</label>';
$html .= '<label><input type="checkbox" name="picf" '.$chkv[$foldattrs['picf']].'> PicFrame capable</label>';
$html .= '<input type="hidden" name="fld" value="'.$foldp.'">';
$html .= '<input type="hidden" name="fldsv" value="1">';
$html .= '<input type="hidden" name="fold" value="1">';

echo $html;
