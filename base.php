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
<link rel="stylesheet" href="<?=$base?>/css/base.css">
<style>
.imgs img {
	width:<?=$cfg->thms->w?>px;
	height:<?=$cfg->thms->h?>px;
}
.fold img {
	width:<?=$cfg->flds->w?>px;
	height:<?=$cfg->flds->h?>px;
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
<gver>1.6</gver>
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
