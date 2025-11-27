<?php

class ImageProc
{
	protected $src = null;
	protected $img_width;
	protected $img_height;
	protected $img_type;

	/*
	needed actions to correctly orient an image based on its current orientation
	array(<rotate angle>, <mirror>)
	actions: rotate, flip ⬍ , flop ⬌

	  1        2       3      4         5            6           7          8

	888888  888888      88  88      8888888888  88                  88  8888888888
	88          88      88  88      88  88      88  88          88  88      88  88
	8888      8888    8888  8888    88          8888888888  8888888888          88
	88          88      88  88
	88          88  888888  888888
	*/

	protected $orientAction = [
		1 => [0, false],	// <none>
		2 => [0, true],		// flop
		3 => [180, false],	// rotate(180) or flip,flop
		4 => [180, true],	// flip
		5 => [270, true],	// rotate(-90), flop
		6 => [270, false],	// rotate(-90)
		7 => [90, true],	// rotate(90), flip
		8 => [90, false]	// rotate(90)
	];

	public static function getImgProc ($src, $imp='')
	{
		if (!$imp) {
			$imp = 'gd';	// default to GD
			if (class_exists('Imagick')) {
				$imp = 'imx';
			} else {
				$sps = explode(':', getenv('PATH'));
				foreach ($sps as $sp) {
					if (file_exists($sp.'/convert')) $imp = 'im';
					// override convert if magick is present
					if (file_exists($sp.'/magick')) $imp = 'im7';
				}
			}
		}
	//	$imp='gd';
		require_once 'graphic'.$imp.'.php';
		return new ImageProcessor($src);
	}

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
			$h = round($dW / $sar);
		} elseif ($sar < $dar) {
			$w = round($dH * $sar);
		}
		return [$w,$h];
	}

	// adjust a source dimension to completely fill a destination dimension, keeping aspect (will need clipping)
	protected function fillInRect ($sW, $sH, $dW, $dH)
	{
		$sar = $sW/$sH;
		$dar = $dW/$dH;
		$w = $sW;
		$h = $sH;
		$x = $y = 0;
		if ($sar > $dar) {
			$w = (int)round($sH * $dar);
			$x = ($sW-$w)>>1;
		} elseif ($sar < $dar) {
			$h = (int)round($sW / $dar);
			$y = ($sH-$h)>>1;
		}
		return [$w,$h,$x,$y];
	}

	// call to get new attribuutes for modified file
	protected function refresh ()
	{
		list($this->img_width, $this->img_height, $this->img_type) = getimagesize($this->src);
	}

}
