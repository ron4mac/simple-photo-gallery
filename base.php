<?php
$ssdly = empty($cfg->ssdly) ? 6000 : (int)($cfg->ssdly*1000);
$descQ = $cfg->desc ? ' <i class="fa fa-question-circle-o" onclick="showDesc()"></i>' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=$cfg->title?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.36/dist/fancybox/fancybox.css">
<style>
h2 i.fa {font-size:1rem}
nav {font-size:larger;}
nav img {vertical-align:text-bottom;}
nav i {font-size: medium; float: right}
gver {display: none}
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
	transform:translate(-50%,-40%);
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
<link rel="stylesheet" href="<?=$base?>/css/<?=$cfg->css?>.css">
<?=$hdinc?>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.36/dist/fancybox/fancybox.umd.js"></script>
<script src="<?=$base?>/js/echo.min.js"></script>
<script>
const mmg_cdir = "<?=$cdir?>";
const gal_desc = "<?=str_replace('"','\"',$cfg->desc)?>";
const h5uOptions = {upURL:'<?=$upURL?>', payload:{imgdir:mmg_cdir}, maxFilesize:0, maxchunksize:<?=$phpmxu-2048?>, doneFunc:(okC, errC, msgC) => uploadDone(okC, errC, msgC)};
const showDesc = () => {
	doAcDlg(gal_desc);
};
<?php
if (isset($headScript) && $headScript) {
	echo implode("\n", $headScript);
}
?>
</script>
</head>
<body>
<gver>1.4</gver>
<?php echo '<header><h2>'.$cfg->title.$descQ.'</h2>'.(empty($acmds)?'':$acmds).'</header>'; ?>
<?php echo '<nav>'.(empty($nav)?'':$nav).'</nav>'; ?>
<?php echo '<section>'.(empty($content)?'':$content).'</section>'; ?>

<?php //echo $html; ?>
<?php //echo'<xmp>';var_dump($GLOBALS);echo'</xmp>'; ?>
<script>
var acDlg = document.getElementById('acDlg');
var acDlgCB = null;
acDlg.addEventListener('close', (e)=>acDlgCB(e));
function doAcDlg (msg, conf=false, cb=()=>{}) {
	acDlg.className = conf ? 'conf' : 'alrt';
	acDlg.querySelector('div').innerHTML = msg;
	acDlgCB = cb;
	acDlg.showModal();
}

Fancybox.bind("[data-fancybox]", {
	// Your custom options
	Slideshow: {
		timeout: <?=$ssdly?>,
		progressParentEl: (slideshow) => {
			return slideshow.instance.container;
		}
	},
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
