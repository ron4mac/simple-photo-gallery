<?php
$appbase = dirname($_SERVER['REQUEST_URI']).'/';
$gobuild = true;
if (!file_exists('.auth.php')) {
	if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']!=$_SERVER['PHP_AUTH_PW']) {
		header('WWW-Authenticate: Basic realm="Gallery Builder"');
		header('HTTP/1.0 401 Unauthorized');
		echo '<h2 style="padding:4rem;text-align:center">You must authenticate use of this feature</h2>';
		exit;
	} else {
		//header('HTTP/1.0 401 Unauthorized'); die();
		$ckid = md5($_SERVER['PHP_AUTH_USER']."\t".$_SERVER['PHP_AUTH_PW']);
		file_put_contents('.auth.php', '<?php $ckid = "'.$ckid.'"; ?>');
		setcookie($ckid, 1, 0, $appbase);
	}
} else {
	include '.auth.php';
	if (!isset($_COOKIE[$ckid])) {
		$gobuild = false;
		if (isset($_POST['unam'])) {
			$ck = md5($_POST['unam']."\t".$_POST['pass']);
			if ($ck == $ckid) {
				setcookie($ckid, 1, 0, $appbase);
				$gobuild = true;
			}
		}
	}
}
if ($gobuild) {
	header('Location: '.$appbase.'build.php', true, 301);
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Gallery Builder</title>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<style>input {font-size:large}</style>
	</head>
	<body>
		<div class="path">
			<form action="" method="post" style="margin-top:5rem;text-align:center">
				<input name="unam" value="" placeholder="Username" required autofocus>
				<input type="password" name="pass" value="" placeholder="Password" required>
				<input type="submit" value="Authenticate">
			</form>
		</div>
	</body>
</html>
