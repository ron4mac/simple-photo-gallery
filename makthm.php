<?php

function makeThumb ($simg, $tpth)
{
	global $cfg;

	list($w,$h,$t) = getimagesize($simg);
	$src_img = getimgRes($simg, $t);
	$w = imagesx($src_img);
	$h = imagesy($src_img);
	list($nw,$nh,$x,$y) = $h>$w
	//	? inFrameRect($w,$h,PFDW,PFDH)
		? frameRect($w,$h,PFDW,PFDH)
		: frameRect($w,$h,PFDW,PFDH);
//	echo "$w,$h : $nw,$nh,$x,$y<br>";
	$dst_img = createImage(PFDW, PFDH, null);

	if (false & $h>$w) {
		file_put_contents('XY.txt', print_r([$x, $y, 0, 0, $nw, $nh, $w, $h],true));
	//	$result = imagecopyresampled($dst_img, $src_img, $x, $y, 0, 0, $nw, $nh, $w, $h);
		$result = imagecopyresampled($dst_img, $src_img, 0, 0, $x, $y, $w, $h, $nw, $nh);
		if (!$result) {
			$result = @imagecopyresized($dst_img, $src_img, $x, $y, 0, 0, $nw, $nh, $w, $h);
		}
	} else {
		$result = imagecopyresampled($dst_img, $src_img, 0, 0, $x, $y, PFDW, PFDH, $nw, $nh);
		if (!$result) {
			$result = @imagecopyresized($dst_img, $src_img, 0, 0, $x, $y, PFDW, PFDH, $nw, $nh);
		}
	}
	imagejpeg($dst_img, $tpth, $cfg->thms->q);
}

function frameRect ($sW, $sH, $dW, $dH)
{
	// get the size ratio for each
	$sar = $sW/$sH;
	$dar = $dW/$dH;
	// default to perfect fit
	$fW = $sW;
	$fH = $sH;
	$x = 0;
	$y =0;

	if ($dar>$sar) {
		$fH = round($sW/$dar);
		$y = ($sH-$fH)>>1;
	}
	if ($sar>$dar) {
		$fW = round($sH*$dar);
		$x = ($sW-$fW)>>1;
	}
	return [$fW, $fH, $x, $y];
}
	
function inFrameRect ($sW, $sH, $dW, $dH)
{
	// get the size ratio for each
	$sar = $sW/$sH;
	$dar = $dW/$dH;
	$fH = $dH;
	$fW = round($sW*$dH/$sH);
	$x = ($dW-$fW)>>1;

	return [$fW, $fH, $x, 0];
}
	
function getimgRes ($simg, $type)
{
	switch ($type) {
	case 1:
		$im = imagecreatefromgif($simg);
		break;
	case 2:
		$im = imagecreatefromjpeg($simg);
		break;
	case 3:
		$im = imagecreatefrompng($simg);
		break;
	}

	// orient if needed
	$flp = 0; $rot = 0;
	$exif = @exif_read_data($simg);
	if (!$exif) return $im;
	if (!isset($exif['Orientation'])) return $im;
	$ort = $exif['Orientation'];
	switch ($ort) {
		case 1: // nothing
			break;
		case 2: // horizontal flip
			$flp = 1;
			break;
		case 3: // 180 rotate left
			$rot = 180;
			break;
		case 4: // vertical flip
			$flp = 2;
			break;
		case 5: // vertical flip + 90 rotate right
			$flp = 2;
			$rot = -90;
			break;
		case 6: // 90 rotate right
			$rot = -90;
			break;
		case 7: // horizontal flip + 90 rotate right
			$flp = 1;
			$rot = -90;
			break;
		case 8: // 90 rotate left
			$rot = 90;
			break;
	}
	if (($flp + $rot) !== 0) {
		try {
			if ($flp==1) {
				$_mirror('h', $im);
			} else if ($flp==2) {
				$_mirror('v', $im);
			}
			if ($rot!==0) {
				$rimg = imagerotate($im, $rot, 0);
				imagedestroy($im);
				$im = $rimg;
			}
		}
		catch(Exception $e) {
		//	die('Error when orienting image: ' . $e->getMessage());
			$this->errs[] = 'Error when orienting image: ' . $e->getMessage();
		}
	}

	return $im;
}
	
function createImage ($new_w, $new_h, $matte)
{
	if ($matte) {
		return imagecreatefromjpeg(JPATH_COMPONENT.'/static/img/'.$matte);
	}
	if (function_exists('imagecreatetruecolor')) {
		$retval = imagecreatetruecolor($new_w, $new_h);
	}

	if (!$retval) {
		$retval = imagecreate($new_w, $new_h);
	}

	return $retval;
}

function _mirror ($how, &$res)
{
	$width = imagesx($res);
	$height = imagesy($res);

	switch ($how) {
		case 'h':
			$src_x = $width -1;
			$src_y = 0;
			$src_width = -$width;
			$src_height = $height;
			break;
		case 'v':
			$src_x = 0;
			$src_y = $height -1;
			$src_width = $width;
			$src_height = -$height;
			break;
	}

	$new = imagecreatetruecolor($width, $height);

	if (imagecopyresampled($new, $res, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height)) {
		imagedestroy($res);
		$res = $new;
	}
}
