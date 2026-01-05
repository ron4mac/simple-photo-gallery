<?php
if (!defined('PFDW')) {
	define('PFDW', 1280);
	define('PFDH', 800);
	define('PFWX', 0);
}
if (!defined('IMGBKG')) {
	define('IMGBKG', $droot.$base.'/css/bg_12.jpeg');
}

class FrameImage
{
	protected $src = null;
	protected $img_width;
	protected $img_height;
	protected $img_type;

	/*
	needed actions to correctly orient an image based on its current orientation
	array(<rotate angle>, <mirror>)
	actions: rotate, flip ⬍ , flop ⬌

	  1		   2	   3	  4			5			 6			 7			8

	888888	888888		88	88		8888888888	88					88	8888888888
	88			88		88	88		88	88		88	88			88	88		88	88
	8888	  8888	  8888	8888	88			8888888888	8888888888			88
	88			88		88	88
	88			88	888888	888888
	*/
/*
	protected $orientAction = [
		1 => [0, false],	// <none>
		2 => [0, true],		// flop
		3 => [180, false],	// rotate(180) or flip,flop
		4 => [180, true],	// flip
		5 => [-90, true],	// rotate(-90), flop
		6 => [-90, false],	// rotate(-90)
		7 => [90, true],	// rotate(90), flip
		8 => [90, false]	// rotate(90)
	];

	public function __construct ($src)
	{
		$this->src = $src;
		list($this->img_width, $this->img_height, $this->img_type) = getimagesize($src);
		if (!$this->img_width && !$this->img_height && !$this->img_type) throw new Exception('The image type is not supported');
	}

	// adjust a source dimension to just fit in a destination dimension, keeping aspect
	protected function fitInRect ($sW, $sH, $dW, $dH)
	{
		$sar = $sW/$sH;
		$dar = $dW/$dH;
		$w = $dW;
		$h = $dH;
		if ($sar > $dar) {
			$h = (int)($dW / $sar);
		} elseif ($sar < $dar) {
			$w = (int)($dH * $sar);
		}
		return [$w,$h];
	}

	// call to get new attribuutes for modified file
	protected function refresh ()
	{
		list($this->img_width, $this->img_height, $this->img_type) = getimagesize($this->src);
	}
*/
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

	function makeFimg ($simg)
	{
		if (!file_exists($simg)) {
			header('HTTP/1.1 410 File missing');
			exit;
		}
		list($w,$h,$t) = getimagesize($simg);
		$src_img = $this->getimgRes($simg, $t);
		// use the w/h from the possibly rotated image
		$w = imagesx($src_img);
		$h = imagesy($src_img);
		list($dw,$dh,$dx,$dy,$sx,$sy) = $this->fitRect($w,$h,PFDW,PFDH,0);
		$dst_img = $this->createImage(PFDW, PFDH, $h>$w ? IMGBKG : null);
		//                                               dest left/top   src left/top   dest width/height   src width/height
		$result = imagecopyresampled($dst_img, $src_img,    $dx, $dy,      $sx, $sy,        $dw, $dh,           $w, $h);
		if (!$result) {
			$result = @imagecopyresized($dst_img, $src_img, $dx, $dy, $sx, $sy, $dw, $dh, $w, $h);
		}
		imagejpeg($dst_img, null, 90);
	}

	// fit the full source image within the destination (no clipping)
	private function fitRect ($sW, $sH, $dW, $dH)
	{
		// get the size ratio for each
		$sar = $sW/$sH;
		$dar = $dW/$dH;
		// default to perfect fit
		$fW = $dW;
		$fH = $dH;
		$x = 0;
		$y = 0;

		if ($dar>$sar) {	// destination is proportionately wider
			$fW = round($fH*$sar);	// adjust dW to match source
			$x = ($dW-$fW)>>1;	// adjust to split extra width between left and right
		}
		if ($sar>$dar) {	// source is proportionately wider
			$fH = round($fW/$sar);	// adjust dH to match source
			$y = ($dH-$fH)>>1;	// adjust to split extra height between top and bottom
		}
		return [$fW, $fH, $x, $y, 0, 0];
	}
/*
	// size to completely fill the destination rect, keeping aspect with clipping
	// returns the dimensions needed to fit and the offsets for centering
	private function frameRect ($sW, $sH, $dW, $dH)
	{
		// get the size ratio for each
		$sar = $sW/$sH;
		$dar = $dW/$dH;
		// default to perfect fit
		$fW = $sW;
		$fH = $sH;
		$x = 0;
		$y = 0;
		$ox = 0;
	
		if (false && $dar>$sar) {
			$fH = round($sW/$dar);
			$y = ($sH-$fH)>>1;
		}
		if ($sar>$dar) {
			$fW = round($sH*$dar);
			$x = ($sW-$fW)>>1;
		}
		return [$fW, $fH, $x, $y, $dW-$fW, 0];
	}

	// size to fit completely in rect .. portrait in landscape here
	// $xP (0..1) amount of blank space on sides to expand into, clipping top and bottom
	private function inFrameRect ($sW, $sH, $dW, $dH, $xP)
	{
		$sar = $sW/$sH;		//size width ratio
		$fH = $dH;
		$fW = round($sW*$dH/$sH);
		$pwa = (int)(($dW-$fW)*$xP);
		$pha = (int)($pwa/$sar);
		$dx = ($dW-$fW-$pwa)>>1;

		$nw = $fW+$pwa;
		$nh = (int)(($fW+$pwa)/$sar);
		$nhs = (int)($pha*$sH/$nh);
		$sy = $nhs>>1;
		return [$nw, $nh, $dx, 0, 0, $sy];
	}
*/
	private function getimgRes ($name, $type)
	{
		switch ($type) {
			case 1:
				$im = imagecreatefromgif($name);
				break;
			case 2:
				$im = imagecreatefromjpeg($name);
				break;
			case 3:
				$im = imagecreatefrompng($name);
				break;
			}

			$exif = exif_read_data($name);
			if ($exif && isset($exif['Orientation'])) {
				$orientation = $exif['Orientation'];
				if ($orientation != 1) {
					$deg = 0;
					switch ($orientation) {
						case 3: $deg = 180; break;
						case 6: $deg = 270; break;
						case 8: $deg = 90; break;
					}
					if ($deg) {
						$im = imagerotate($im, $deg, 0);
					}
				}
			}

		return $im;
	}

	private function newImg ($new_w, $new_h)
	{
		if (function_exists('imagecreatetruecolor')) return imagecreatetruecolor($new_w, $new_h);
		return imagecreate($new_w, $new_h);
	}

	private function createImage ($new_w, $new_h, $matte)
	{
		if ($matte) {
		//	$m = imagecreatefromjpeg(JPATH_COMPONENT.'/static/img/'.$matte);
			if (file_exists($matte)) {
				$m = imagecreatefromjpeg($matte);
			} else {
				$m = $this->newImg(PFDW, PFDH);
			}
			$im = $this->newImg($new_w, $new_h);
			$result = imagecopyresampled($im, $m, 0, 0, 0, 0, $new_w, $new_h, imagesx($m), imagesy($m));
			if (!$result) {
				$result = @imagecopyresized($im, $m, 0, 0, 0, 0, $new_w, $new_h, imagesx($m), imagesy($m));	//$new_w, $new_h);
			}
			return $im;
		}
		return $this->newImg($new_w, $new_h);
	}

}
