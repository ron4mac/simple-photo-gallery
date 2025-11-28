<?php
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
		setcookie($ckid, 1, 0, '/gallery/');
	}
}
//} else {
	header('Location: /gallery/build.php', true, 301);
//}
