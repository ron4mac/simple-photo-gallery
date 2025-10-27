<?php
$msg = '';
$alert = '';
$ask = 'true';
if (isset($_POST['galloc'])) {
	while (true) {
		$ask = 'false';
		$droot = $_SERVER['DOCUMENT_ROOT'].'/';
		$gbase = $droot.$_POST['galloc'];
		if (file_exists($gbase)) {
			$ask = 'true';
			$alert = "Directory '{$_POST['galloc']}' already exists.";
			break;
		}
		mkdir($gbase, 0777, true);
		$gbase .= '/';
		$cfg = (object) [
			'thms' => (object) ['w'=>240,'h'=>180, 'q'=>90],
			'flds' => (object) ['w'=>128,'h'=>128],
			'css' => 'dark',
			'title' => $_POST['galnam'],
			'desc' => 'A new gallery built with the gallery builder',
			'auth_users' => (object) [$_POST['admnam']=>password_hash($_POST['admpass'], null)]
		];
	
		// figure out this base location relative to root
		$docr = $_SERVER['DOCUMENT_ROOT'];
		$rloc = str_replace($docr, '', __DIR__);
		if (strlen($rloc)==strlen(__DIR__)) {
			// it's one of THOSE servers .. try to figure it out
			$tmp = $docr;
			while (strpos($rloc,basename($tmp))) {
				$tmp = dirname($tmp);
			}
			$docr = str_replace($tmp, '', $docr);
			$rloc = substr(strstr($rloc,$docr), strlen($docr)+1);
		}
	
		file_put_contents($gbase.'config.json', json_encode($cfg, JSON_PRETTY_PRINT));
		$data = file_get_contents(__FILE__, false, NULL, __COMPILER_HALT_OFFSET__);
		$ifiles = explode('&&&&',$data);
		foreach ($ifiles as $f) {
			list($fp, $fd) = explode('====',$f);
			$fd = str_replace('####', $rloc, $fd);
			if (dirname($fp) !== '.') mkdir($gbase.dirname($fp), 0777, true);
			file_put_contents($gbase.$fp, $fd);
		}
		mkdir($gbase.'media', 0777);
		$msg .= '<h4><a href="../'.$_POST['galloc'].'/admin">Go there</a></h4>';
		break;
	}
}

function pVal ($n)
{
	return isset($_POST[$n]) ? htmlspecialchars($_POST[$n]) : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gallery Instance Creation</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
<style>
body {
	background-color: gray;
}
dialog {
	margin-top: 16rem;
	border-radius: 5px;
}
form {
	display: grid;
	row-gap: .5rem;
}
input[type="text"] {
	width: 20rem;
}
.dbuts {
	border-top: 1px solid #CCC;
	padding-top: 1rem;
}
.dbuts input {
	float: right;
	margin-left: .5rem;
}
</style>
<script>
const ask = <?=$ask?>;
const alrt = "<?=$alert?>";
//if (alrt) alert(alrt);
if (ask) {
	document.addEventListener('DOMContentLoaded', function() {
		const dlg = document.getElementById('newGdlg');
		dlg.showModal();
		if (alrt) setTimeout(alert(alrt), 2500);
	});
}
</script>
</head>
<body style="background-color:gray">
<?=$msg?>
<dialog id="newGdlg">
	<form action="" method="POST">
		<label>
			Gallery Name:<br>
			<input type="text" class="textin" name="galnam" value="<?=pVal('galnam')?>" required autofocus>
		</label>
		<label>
			Server Location:<br>
			<input type="text" class="textin" name="galloc" value="<?=pVal('galloc')?>" required>
		</label>
		<label>
			Admin User Name:<br>
			<input type="text" class="textin" name="admnam" value="<?=pVal('admnam')?>" required>
		</label>
		<label>
			Admin Password:<br>
			<input type="text" class="textin" name="admpass" value="<?=pVal('admpass')?>" required>
		</label>
		<div class="dbuts">
			<input type="submit" value="Create">
			<input type="reset" value="Cancel">
		</div>
	</form>
</dialog>
</body>
</html>
<?php __halt_compiler()?>
index.php====<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
$gbase = dirname(__FILE__);
$gbases = $gbase.'/';
$rqf = empty($_GET['f']) ? 'viewer.php' : 'thumb.php';
require $droot.'/####/'.$rqf;
&&&&admin.php====<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
$gbase = dirname(__FILE__);
$gbases = $gbase.'/';
session_name('mmg'.substr(sha1($gbase), -30));
session_start();
(isset($_SESSION['logged']) || !empty($_GET['_pfu_'])) or die('Not authorized');
define('ADM',1);
require $droot.'/####/admin.php';
&&&&admin/index.php====<?php
define('LIREQ',1);
$droot = $_SERVER['DOCUMENT_ROOT'];
require $droot.'/####/admin.php';
exit();
&&&&picframe/index.php====<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
$gbase = dirname(dirname(__FILE__));
$gbases = $gbase.'/';
define('IBASE','media/');
require $droot.'/####/picframe.php';

