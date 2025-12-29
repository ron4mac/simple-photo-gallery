<?php
if (isset($_GET['pw'])) {
	echo password_hash($_GET['pw'], null);
	exit();
}
if (defined('LIREQ')) {
	$cfg = json_decode(file_get_contents('../config.json'), true);
	if (isset($_POST['pass'])) {
		$pass = $_POST['pass'];
		$auth_pass = $cfg['passw'];
		if (password_verify($pass, $auth_pass)) {
			session_name('mmg'.substr(sha1(dirname(getcwd())), -30));
			session_start();
			$_SESSION['logged'] = md5(dirname(getcwd()));
			header('Location: ../');
			exit();
		}
	}

	?>
	<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="utf-8">
			<title>Gallery Manager</title>
			<meta name="viewport" content="width=device-width,initial-scale=1">
		</head>
		<body>
			<div class="path">
				<form action="" method="post" style="margin:10px;text-align:center">
					<input type="password" name="pass" value="" placeholder="Password" required>
					<input type="submit" value="Login">
				</form>
			</div>
		</body>
	</html>
	<?php
	exit();
}

defined('ADM') or die('Not authorized');
define('CFGFILE','config.json');
define('IBASE', 'media/');
if (isset($_POST['faex'])) {
	$cfg = json_decode(file_get_contents(CFGFILE));
	require 'upload.php';
	$upld = new Up_Load(['target_dir'=>IBASE.$_POST['imgdir'],'cfg'=>$cfg]);
	exit();
}
if (isset($_POST['newf'])) {
	$dirn = $_POST['newf'];
	mkdir(IBASE.$dirn);
	exit();
}
if (isset($_POST['cfg'])) {
	require 'config.php';
	exit();
}
if (isset($_POST['fold'])) {
	require 'fold.php';
	exit();
}
if (isset($_POST['kgal']) && $_POST['kgal']==$gbase) {
	echo delTree($gbase) ? '' : 'x';
	exit();
}

if (isset($_GET['fsel'])) {
	$fdirs = ['<select><option value="">HOME</option>'];
	foldHeir(IBASE, $fdirs, 1);
	echo implode('', $fdirs).'</select>';
}

if (isset($_GET['delm'])) {
	$pinput = file_get_contents('php://input');
	$rvars = json_decode($pinput);
	$dir = IBASE.$rvars->dir;
	foreach ($rvars->files as $fn) {
		unlink($dir.$fn);
		unlink($dir.'.thm/'.$fn);
	}
	foreach ($rvars->folds as $dn) {
		delTree($dir.$dn);
	}
}

if (isset($_GET['melm'])) {
	$pinput = file_get_contents('php://input');
	$rvars = json_decode($pinput);

	$sdir = IBASE.$rvars->sdir;
	$ddir = IBASE.$rvars->ddir;	//				($rvars->ddir ? ($rvars->ddir.'/') : '');
	// move the file items
	// * may want to force thumb regen after files move
	foreach ($rvars->files as $fn) {
		moveFile($sdir.$fn, $ddir.$fn);
	}
	// move the folder items
	foreach ($rvars->folds as $dn) {
		moveFile($sdir.$dn, $ddir.$dn);
	}
}

function foldHeir ($dir, &$dirs, $l)
{
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		if ($file[0]=='.') continue;
		$fp = "$dir/$file";
		if (is_dir($fp)) {
			$d = ltrim(preg_replace('#^media#', '', $fp),'/');
			if ($d) $d .= '/';
			$dirs[] = '<option value="'.$d.'">'.str_repeat('&mdash;',$l).' '.$file.'</option>';
			foldHeir($fp, $dirs, $l+1);
		}
	}
}

function moveFile ($from, $to)
{
	$fnp = pathinfo($to);
	$base_name = $fnp['filename'];
	$ext = isset($fnp['extension']) ? ('.'.$fnp['extension']) : '';
	$uniq = '';
	$nr = 0;

	while (file_exists($fPath.$base_name.$uniq.$ext)) {
		$uniq = '~'.$nr++;
	}
	$ffpnam = $fPath.$base_name.$uniq.$ext;
	return rename($from, $to);
}

function delTree ($dir)
{
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
	}
	return rmdir($dir);
}

function deThumb ($dir)
{
	if (is_dir($dir) && ($handle = opendir($dir))) {
		while (false !== ($file = readdir($handle))) {
			if( $file == '.' || $file == '..') continue;
			if (is_dir($dir.$file)) {
				$dir2 = $dir.$file;
				if ($file == '.thm') {
					delTree($dir2);
				} else {
					deThumb($dir2.'/');
				}
			}
		}
		closedir($handle); 
	}
}

