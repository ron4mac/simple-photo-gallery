<?php
require 'head.php';
define('IBASE','media/');

$upURL = 'admin.php';
if (isset($_POST['picup'])) {
	$_GET['d'] = $_POST['fldp'];
	$isPubUp = true;
	$upURL .= '?_pfu_=1';
}

if (isset($hdinc)) {
	$hdinc .= '<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
} else {
	$hdinc = '<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
}
$acmds = '';
$headScript = [];
$phpmxu = 0;
$updone = 'if (!errC) window.location.reload(true);';
$fldsets = (object)['desc' => '','pubup' => 0,'picf' => 0];
function parse_size ($size)
{
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
	$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
	if ($unit) {
		// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	} else {
		return round($size);
	}
}
function file_upload_max_size ()
{
	static $max_size = -1;
	if ($max_size < 0) {
		$post_max_size = parse_size(ini_get('post_max_size'));
		if ($post_max_size > 0) {
			$max_size = $post_max_size;
		}
		$upload_max = parse_size(ini_get('upload_max_filesize'));
		if ($upload_max > 0 && $upload_max < $max_size) {
			$max_size = $upload_max;
		}
	}
	return $max_size;
}

if ($isLogged) {
	$acmds = '<span class="acmds">
<button class="newFbut" onclick="askNewF()">New Folder</button>
<button onclick="askUpld()">Upload</button>
<button onclick="setDelete(this)">Delete</button>
<button onclick="doCfg()">Config</button>
<i class="fa fa-lg fa-trash" onclick="delGalQ()"></i>
</span>';
	$hdinc .= '
<link rel="stylesheet" href="'.$base.'/css/admin.css">
<script>const appB="'.$base.'"</script>
<script src="'.$base.'/js/admin.js" defer></script>';
	$phpmxu = file_upload_max_size();
} else if (isset($isPubUp)) {
//	$acmds = '<span class="acmds">
//<button onclick="askUpld()">Upload</button>
//</span>';
	$hdinc .= '
<link rel="stylesheet" href="'.$base.'/css/admin.css">
<style>.pupbtn{margin-left:1rem}</style>
<script>const appB="'.$base.'"</script>
<script src="'.$base.'/js/admin.js" defer></script>';
	$phpmxu = file_upload_max_size();
}

$self = htmlentities($_SERVER['PHP_SELF']);

$finfo = finfo_open(FILEINFO_MIME);
if (!$finfo) die('Opening fileinfo database failed');


function f2fn ($f)
{
	// get the base file name and strip any leading numbers
	$fp = pathinfo($f);
	return preg_match('#^\d+(.*)$#', $fp['filename'], $m) ? $m[1] : $fp['filename'];
}

$cdir = empty($_GET['d']) ? '' : (htmlentities($_GET['d']) . '/');
$dirts = $cdir;
$curD = '';
$nav = '';
if (file_exists(IBASE . $dirts . '.fold')) {
	$attrs = json_decode(file_get_contents(IBASE . $dirts . '.fold'), true);
	foreach($attrs as $k=>$v) {
		$fldsets->{$k} = $v;
	}
}

$navH = '<span class="fa fa-home" aria-hidden="true">HOME</span>';
if (isset($isPubUp)) {
	$nav .= basename($cdir);
} elseif ($cdir) {
	$hico = $navH;
	$href = './';	//htmlentities($_SERVER['SCRIPT_URL']);
	$nav .= '<a href="'.$href.'">'.$hico.'</a>';
	$href = './?d=';
	$folds = explode('/', $cdir);
	array_pop($folds);
	$curD = array_pop($folds);
	foreach ($folds as $fold) {
		$href .= $fold;
		$nav .= ' / <a href="'.$href.'">'.$fold.'</a>';
		$href .= '/';
	}
	$nav .= " / $curD";
} else {
	$nav .= $navH;
}
if (empty($isPubUp) && $isLogged) {
	if ($fldsets->pubup) $nav .= ' <span class="fa fa-cloud-upload" aria-hidden="true"></span>';
	if ($fldsets->picf) {
		$prot = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
		$uri = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'],'/'));
		$plk = base64_encode($prot . "://" . $_SERVER['HTTP_HOST'] . $uri . '/picframe/?act=plist&fld=' . urlencode($dirts));
		$nav .= ' <a href="http://picframe.local/static/cgetnpl.html?nplt='.urlencode(basename($dirts)).'&nplk='.$plk.'" class="gopic" target="_blank"><span class="fa fa-picture-o"></span></a>';
	}
}
if ($isLogged) {
	$nav .= '<i class="fa fa-cog pullr" aria-hidden="true" onclick="foldset(\''.$cdir.'\')"></i>';
} else if (isset($isPubUp)) {
	$nav .= '<span class="pupbtn"><button onclick="askUpld()">Upload</button></span>';
}

$content = '';
if ($fldsets->desc) {
	$content .= '<h4>'.$fldsets->desc.'</h4></section><section>';
}

$dirsl = [];
$imgsl = [];

$files = scandir(IBASE . $dirts);
foreach ($files as $file) {
	if ($file[0]=='.') continue;
	$fpath = $dirts.$file;
	if (is_dir(IBASE . $fpath)) {
		$dirsl[] = $file;
		continue;
	}
	$iurl = '?f='.urlencode(/*'/'.*/$fpath);
	$mtyp = substr(finfo_file($finfo,IBASE . $fpath),0,6);
	switch ($mtyp) {
	case 'image/':
		$imgsl[$file] = [$fpath, $iurl];
		break;
	case 'video/':
		$iurl =  $fpath;
		$fn = basename($iurl);
		$imgsl[$file] = [$iurl, $cfg->css=='dark' ? ($base.'/css/videod.svg') : ($base.'/css/video.svg'), f2fn($fn)];
		break;
	case 'audio/':
		// fancybox doesn't directly handle audio so force an iframe
		$imgsl[$file] = ['javascript:;" data-type="iframe" data-src="'.$iurl, $base.'/css/audio.svg'];
		break;
	case 'applic':
		$iurl = $fpath;
		$fn = basename($iurl);
		$imgsl[$file] = [$iurl, $base.'/css/pdf.svg', f2fn($fn)];
		break;
	}
}

$headScript[] = 'const pItems = '.count($dirsl)+count($imgsl).';';

if ($dirsl) {
	$content .= '<div class="folds">';
	natsort($dirsl);
	foreach ($dirsl as $adir) {
		if ($isLogged) $content .= '<div><div class="delbox"><img src="'.$base.'/css/deleterc.svg" data-fold="'.$adir.'" onclick="delToggle(this)"></div>';
		$content .= '<a href="'.$self.'?d='.urlencode($cdir.$adir).'"><div class="fold"><span>'.$adir.'</span>';
		$content .= '<img src="'.$base.'/css/folder.svg" alt=""></div></a>';
		if ($isLogged) $content .= '</div>';
	}
	$content .= '</div>';
}
if ($imgsl) {
	$content .= '<div class="imgs">';
	ksort($imgsl, SORT_NATURAL);	//, SORT_NATURAL | SORT_FLAG_CASE);
	foreach ($imgsl as $file=>$aimg) {
		if (is_array($aimg)) {
			$fn = isset($aimg[2]) ? ('<span class="fname">'.$aimg[2].'</span>') : '';
			if ($isLogged) $content .= '<div><div class="delbox"><img src="'.$base.'/css/deleterc.svg" data-file="'.$file.'" onclick="delToggle(this)"></div>';
			$content .= '<a data-fancybox="gallery" href="'.IBASE.$aimg[0].'"><img src="'.$base.'/css/img.png" data-echo="'.$aimg[1].'" />'.$fn.'</a>';
			if ($isLogged) $content .= '</div>';
		} else {
			$content .= '<div class="mbox"><p>'.$file.'</p>';
			$content .= '<a data-fancybox="gallery" href="'.IBASE.$aimg.'"><img class="aimg" src="'.$aimg.'" /></a></div>';
		}
	}
	$content .= '</div>';
}

$content .= '<br style="clear:both">';
if ($isLogged) $content .= '
<dialog id="newFdlg">
	<form method="dialog" onsubmit="return newFreq(event,this)">
		<label>
			Folder name:
			<input type="text" class="textin" name="newFnam" autocapitalize="off" required autofocus>
		</label>
		<div class="dbutts">
			<input type="reset" value="Cancel" onclick="dlgClose(this)">
			<input type="submit" value="Create">
		</div>
	</form>
</dialog>
<dialog id="cfgDlg">
	<form method="dialog" onsubmit="return saveCfg(event,this)">
		<div id="cfgElms" class="dlgElems"></div>
		<div class="dbutts">
			<input type="submit" class="cfgAdv" value="Advanced">
			<input type="reset" class="cfgClos" value="Cancel" onclick="dlgClose(this)">
			<input type="submit" class="cfgSave" value="Save">
		</div>
	</form>
</dialog>
<dialog id="foldDlg">
	<form method="dialog" onsubmit="return saveFold(event,this)">
		<div id="foldElms" class="dlgElems"></div>
		<div class="dbutts">
			<input type="reset" class="fldClos" value="Cancel" onclick="dlgClose(this)">
			<input type="submit" class="fldSave" value="Save">
		</div>
	</form>
</dialog>
';
if ($isLogged || isset($isPubUp)) $content .= '
<dialog id="uplddlg">
	<div class="xclose" onclick="parentElement.close()"><img src="'.$base.'/css/delete.svg"></div>
	<label for="faex">When file already exists: </label>
	<select id="faex" name="faex">
		<option value="f" selected>Fail</option>
		<option value="r">Rename</option>
		<option value="o">Overwrite</option>
	</select>
	<div id="uplodr"></div>
</dialog>
';
$content .= '
<dialog id="acDlg" class="alrt">
	<div></div>
	<div class="dbutts">
		<button value="cncl" onclick="this.closest(\'dialog\').close(this.value)" class="canbut">Cancel</button>
		<button value="okay" onclick="this.closest(\'dialog\').close(this.value)">Okay</button>
	</div>
</dialog>
';

require 'base.php';
