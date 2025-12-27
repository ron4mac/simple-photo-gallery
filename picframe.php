<?php

if (isset($_GET['act'])) {
//	file_put_contents('log.txt', print_r([time(),$_GET], true), FILE_APPEND);
	try {
		header('Access-Control-Allow-Origin: *');
		switch ($_GET['act']) {
		case 'plist':
			getPlayList($_GET['fld'], false, isset($_GET['pco']));
			break;
		case 'thms':
			getPlayList($_GET['fld'], true);
			break;
		case 'getimg':
			$cfg = json_decode(file_get_contents('../config.json'));
			if (isset($cfg->bgi)) {
				define('IMGBKG', $droot.$base.'/css/bg_'.$cfg->bgi.'.jpeg');
			}
			if (isset($_GET['dim'])) {
				list($iw,$ih) = preg_split('/[\.x]/', $_GET['dim']);
				define('PFDW', $iw);
				define('PFDH', $ih);
				define('PFWX', isset($cfg->pexp) ? $cfg->pexp/100 : 0);
				require 'classes/frameimg.php';
				$imgp = new FrameImage();
				header('Content-Type: image/jpeg; charset=utf-8',true);
				$imgp->makeFimg($gbases.IBASE.$_GET['img']);
				break;
			}
			header('Content-Type: image/jpeg; charset=utf-8',true);
			readfile($gbases.IBASE.$_GET['img']);
			break;
		default:
			throw new Exception('UNKNOWN ACTION');
		}
		exit();
	} catch (\Throwable $e) {
		header('HTTP/1.1 '.(400+$e->getCode()).' Action Failed: '.$_GET['act']);
		file_put_contents('catch.err.txt', print_r($e, true), FILE_APPEND);
		exit($e->getMessage());
	}
}

function getPlayList ($fld, $thms=false, $pco=false)
{
	global $gbases;

	$pics = $pco ? 0 : [];

	$prot = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
	$uri = strtok($_SERVER['REQUEST_URI'], '?');
	$plk = $prot . "://" . $_SERVER['HTTP_HOST'] . $uri . '?act=getimg&img=';

	$fp2d = $gbases.IBASE.$fld;
	if (is_dir($fp2d) && ($handle = opendir($fp2d))) {
		while (false !== ($file = readdir($handle))) {
			if (str_starts_with($file,'.')) continue;
			if (is_dir($fp2d.$file)) {
			} else {
				if ($thms) {
					echo '<img class="pfthm" src="'.$plk.urlencode($fld.'.thm/'.$file).'">';
				} else {
					if ($pco) {
						$pics++;
					} else {
						$pics[] = $plk.urlencode($fld.$file).'&dim='.(isset($_GET['ddim']) ? $_GET['ddim'] : '1200.600');	//."\n";	//json_encode($fp2d.$file);
					}
				}
			}
		}
		closedir($handle); 
	}
	if ($pco) {
		echo $pics;
		return;
	}
	if (!$thms) {
		shuffle($pics);
		echo "\t\t\t\t" . count($pics) . "\t" . implode("\n",$pics);
	}
}


$hdinc = '
<style>
.availup {
	margin: 2em;
	font-size: x-large;
	display: flex;
	gap: 1rem;
}
.upFold {
	border: 1px solid #CCC;
	border-radius: 5px;
	padding: 1rem 2rem;
	cursor: pointer;
}
</style>
<script>
function doUpload (evt) {
	console.log(evt);
	document.forms.PicUp.elements.namedItem("fldp").value = evt.target.dataset.fld;
	document.forms.PicUp.submit();
}
</script>
';
$content = '';
$flds = [];
$GBADJ = '../';

require 'head.php';

function getFolds ($dir, &$flds)
{
	global $gbases;

	$fp2d = $gbases.IBASE.$dir;
	if (is_dir($fp2d) && ($handle = opendir($fp2d))) {
		while (false !== ($file = readdir($handle))) {
			if ($file == '.' || $file == '..') continue;
			if (is_dir($fp2d.$file)) {
				$dir2 = $dir.$file;
				getFolds($dir2.'/', $flds);
			} else if ($file == '.fold') {
				$flds[$dir] = json_decode(file_get_contents($fp2d.$file));
			}
		}
		closedir($handle); 
	}
}

getFolds(''/*IBASE*/, $flds);

if (empty($flds)) {
	$content .= '<h4>NO AVAILABLE UPLOAD AREAS</h4>';
} else {
	$content .= '<div class="availup" onclick="doUpload(event)">';
	foreach ($flds as $fld=>$v) {
		if ($v->pubup) {
			$f =  basename($fld);
			if (empty($f)) $f = 'HOME';
			$content .= '<span class="upFold" data-fld="'.$fld.'">'.$f.'</span>';
		}
	}
	$content .= '</div>';
	$content .= '	<form action="../" name="PicUp" method="POST">
		<input type="hidden" name="picup" value="1">
		<input type="hidden" name="fldp" value="">
	</form>';
}

$cdir='???';
$phpmxu = 16384;
$upURL = 'admin.php';
require 'base.php';