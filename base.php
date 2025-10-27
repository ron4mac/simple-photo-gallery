<?php
//define('CFGFILE','config.json');
//define('IBASE','media/');

//session_name('mmg'.substr(sha1($gbase), -30));
//session_start();
//$isLogged = isset($_SESSION['logged']);

/*
$cfg = (object) [
	'thms' => (object) ['w'=>240,'h'=>180, 'q'=>90],
	'flds' => (object) ['w'=>128,'h'=>128],
	'css' => 'dark',
	'title' => 'My Media Gallery'
	];
if (file_exists($gbase.CFGFILE)) {									//	<<<< @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	$cfg = json_decode(file_get_contents($gbase.CFGFILE));
} else {
	//file_put_contents(CFGFILE, json_encode($cfg, JSON_PRETTY_PRINT));
}
*/






?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=$cfg->title?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
<style>
nav {font-size:larger;}
nav img {vertical-align:text-bottom;}
nav i {font-size: medium; margin-top: 4px; float: right}
.folds, .imgs {
	clear:both;
	margin-top:1rem;
}
.folds, .imgs {
	display: flex;
	flex-wrap: wrap;
	gap: .5rem;
}
.imgs a {
	position: relative;
}
.imgs img {
	width:<?=$cfg->thms->w?>px;
	height:<?=$cfg->thms->h?>px;
}
.fold img {
	width:<?=$cfg->flds->w?>px;
	height:<?=$cfg->flds->h?>px;
}
.fold {
	position:relative;
/*	float:left;
	margin:0 16px 16px 0;
	padding:0 8px;
	border:2px solid #CCEEFF*/
}
.fold span {
	position:absolute;
	top:50%;
	left:50%;
	font-size: large;
	text-align: center;
	transform:translate(-50%,-50%);
}
.fname {
	display: block;
	width: 100%;
	text-align: center;
	position: absolute;
	bottom: 8px;
}
.dlgElems {
	display: grid;
	padding-right: .5rem;
}
.mbox {
	float:left;
	margin:0 16px 16px 0;
	padding: 0 8px;
	border:1px solid #EEEEEE;
	text-align:center;
}
.mdya {
	background-image:url(../css/avback.png);
	background-color:lightcyan;
}
.mbox img {height:96px;}
.aimg {
	background-image:url(../css/back1.png);
}
.fancybox__carousel .fancybox__slide.has-html5video .fancybox__content {
	width: 100%;
	height: 100%;
}
.fancybox-slide--iframe .fancybox-content {
	min-height : 400px;
	max-width  : 80%;
	max-height : 80%;
	margin: 0;
	background-color: transparent;
}
</style>
<link rel="stylesheet" href="/galbase/css/<?=$cfg->css?>.css">
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script> -->
<?=$hdinc?>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script src="/galbase/js/echo.min.js"></script>
<script>
const mmg_cdir = "<?=$cdir?>";
const h5uOptions = {upURL:'<?=$upURL?>', payload:{imgdir:mmg_cdir}, maxFilesize:0, maxchunksize:<?=$phpmxu-2048?>, doneFunc:(okC, errC, msgC) => uploadDone(okC, errC, msgC)};
function backc (colr) {
	var elms = document.getElementsByClassName("aimg");
	if (colr) {
		for (var i = 0; i < elms.length; i++) {
			elms[i].style.backgroundColor = colr;
			elms[i].style.backgroundImage = "none";
		}
	} else {
		for (var i = 0; i < elms.length; i++) {
			elms[i].style.backgroundColor = "none";
			elms[i].style.backgroundImage = "url(../css/back1.png)";
		}
	}
}
</script>
</head>
<body>
<?php echo '<header><h2>'.$cfg->title.'</h2>'.(empty($acmds)?'':$acmds).'</header>'; ?>
<?php echo '<nav>'.(empty($nav)?'':$nav).'</nav>'; ?>
<?php echo '<section>'.(empty($content)?'':$content).'</section>'; ?>

<?php //echo $html; ?>
<?php //echo'<xmp>';var_dump($GLOBALS);echo'</xmp>'; ?>
<script>
	Fancybox.bind("[data-fancybox]", {
		// Your custom options
		Thumbs: false,
		Toolbar: {
			display: {
				left: ["infobar"],
				middle: [],
				right: ["slideshow", "fullscreen", "download", "close"]
			}
		}
	});
	echo.init({
		offset: 200,
		throttle: 250,
		debounce: false
	});
</script>
</body>
</html>
