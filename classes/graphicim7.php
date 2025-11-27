<?php
include_once 'imgproc.php';

function _log($d) {file_put_contents('LOG.txt',print_r($d,true)."\n",FILE_APPEND);}

class ImageProcessor extends ImageProc
{
	public $ipp = 'IM7';
	protected $errs = [];

	public function __construct ($src)
	{
		parent::__construct($src);
	}

	public function getErrors ()
	{
		return $this->errs;
	}

	// resize to fit/fill a container, keeping apsect
	public function resizeToContainer ($w, $h, $dest, $mth='fit')
	{
		$_s = escapeshellarg($this->src);
		$_d = escapeshellarg($dest);
		switch ($mth) {
		case 'fit':
			list($nw, $nh) = $this->fitInRect($this->img_width, $this->img_height, $w, $h);
			$cmd = "magick {$_s} -resize \"{$nw}x{$nh}>\" -quality 90 {$_d} 2>&1";
			break;
		case 'fill':
			list($nw, $nh) = $this->fillInRect($this->img_width, $this->img_height, $w, $h);
			$wr = $this->img_width/$nw;
			$hr = $this->img_height/$nh;
			$sw = (int)($w*$wr);
			$sh = (int)($h*$hr);
			$cmd = "magick {$_s} -resize \"{$sw}x{$sh}^\" -gravity center -extent {$w}x{$h} -quality 90 {$_d} 2>&1";
			break;
		}
		exec($cmd, $output, $retval);
		return filesize($dest);
	}

	public function createThumb ($dest, $ext, $maxW=120, $maxH=120, $sqr=true)
	{
		if (!isset($this->img_width)) return 0;
		$dfil = $dest.$ext;
		$w = $this->img_width;
		$h = $this->img_height;
		$sr = $w/$h;
		$dr = $maxW/$maxH;
		$sw = $maxW;
		$sh = $maxH;
		$_s = escapeshellarg($this->src);
		$_d = escapeshellarg($dfil);
	//	$cmd = "magick {$_s} -thumbnail {$maxW}x{$maxH}^ -gravity center -extent 120x120 -sharpen 0x1 -quality 90 {$_d}  2>&1";
		$cmd = "magick {$_s} -thumbnail {$sw}x{$sh}^ -gravity center -extent {$maxW}x{$maxH} -sharpen 0x1 -quality 90 {$_d}  2>&1";
		exec($cmd, $output, $retval);
		return filesize($dfil);
	}

	public function createMedium ($dest, $ext, $maxW=1200, $maxH=0)
	{
		if (!isset($this->img_width)) return 0;
		$dfil = $dest.$ext;
		$w = $this->img_width;
		$h = $this->img_height;
		if ($maxW && $maxH) {
			list($w,$h) = $this->fitInRect($this->img_width, $this->img_height, $maxW, $maxH);
		} elseif ($maxH) {
			$r = $w/$h;
			$h = $maxH;
			$w = $r * $maxH;
		} else {
			$r = $h/$w;
			$w = $maxW;
			$h = round($r * $maxW);
		}
		$_s = escapeshellarg($this->src);
		$_d = escapeshellarg($dfil);
		$cmd = "magick {$_s} -resize {$w}x{$h}\> -quality 90 {$_d}  2>&1";
		exec($cmd, $output, $retval);
		return filesize($dfil);
	}

	public function orientImage ($dest)
	{
		$_s = escapeshellarg($this->src);
		$_d = escapeshellarg($dest);
	    $cmd = "magick {$_s} -auto-orient {$_d}  2>&1";
        exec($cmd, $output, $retval);
		$this->src = $dest;
		parent::refresh();
		return filesize($this->src);
	}

}
