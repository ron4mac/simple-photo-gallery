<?php
include_once 'imgproc.php';

function _log($d) {file_put_contents('LOG.txt',print_r($d,true)."\n",FILE_APPEND);}

class ImageProcessor extends ImageProc
{
	public $ipp = 'GD2';
	protected $errs = [];
	protected $res = null;

	public function __construct ($src)
	{
		parent::__construct($src);

		switch ($this->img_type) {
			case 1:
				$this->res = imagecreatefromgif($this->src);
				break;
			case 2:
				$this->res = imagecreatefromjpeg($this->src);
				break;
			case 3:
				$this->res = imagecreatefrompng($this->src);
				break;
		}

		if (!$this->res) throw new Exception('INVALID IMAGE');
	}

	public function getErrors ()
	{
		return $this->errs;
	}

	// resize to fit/fill a container, keeping apsect
	public function resizeToContainer ($w, $h, $dest, $mth='fit')
	{
		switch ($mth) {
		case 'fit':
			list($nw, $nh) = $this->fitInRect($this->img_width, $this->img_height, $w, $h);
			$this->resize($nw, $nh);
			break;
		case 'fill':
			list($nw, $nh, $xo, $yo) = $this->fillInRect($this->img_width, $this->img_height, $w, $h);
			$this->resize($w, $h, $xo, $yo, $nw, $nh);
			break;
		}
		imagejpeg($this->res, $dest, 90);
//		$this->orientImage($dest);
		return filesize($dest);
	}

	public function createThumb ($dest, $ext, $maxW=120, $maxH=120, $sqr=true)
	{
		return $this->resizeToContainer($maxW, $maxH, $dest.$ext, 'fill');


		$new_w = 120;
		$new_h = 120;

		$orig_w = imagesx($this->res);
		$orig_h = imagesy($this->res);

		$w_ratio = ($new_w / $orig_w);
		$h_ratio = ($new_h / $orig_h);

		if ($orig_w > $orig_h ) {//landscape
			$crop_w = round($orig_w * $h_ratio);
			$crop_h = $new_h;
			$src_x = ceil( ( $orig_w - $orig_h ) / 2 );
			$src_y = 0;
		} elseif ($orig_w < $orig_h ) {//portrait
			$crop_h = round($orig_h * $w_ratio);
			$crop_w = $new_w;
			$src_x = 0;
			$src_y = ceil( ( $orig_h - $orig_w ) / 2 );
		} else {//square
			$crop_w = $new_w;
			$crop_h = $new_h;
			$src_x = 0;
			$src_y = 0;
		}

		try {
			$img = imagecreatetruecolor($new_w, $new_h);
			imagecopyresampled($img, $this->res, 0 , 0 , $src_x, $src_y, $crop_w, $crop_h, $orig_w, $orig_h);

			$sharpenMatrix = [[-1, -1, -1],[-1, 16, -1],[-1, -1, -1]];

			// calculate the sharpen divisor
			$divisor = array_sum(array_map('array_sum', $sharpenMatrix));
			$offset = 0;
			// apply the matrix
			imageconvolution($img, $sharpenMatrix, $divisor, $offset);

//			imagedestroy($this->res);
//			$this->res = $img;
//			imagejpeg($this->res, $dest.$ext, 95);
			imagejpeg($img, $dest.$ext, 95);
			imagedestroy($img);
			return filesize($dest.$ext);
		}
		catch(Exception $e) {
		//	die('Error when creating a thumbnail: ' . $e->getMessage());
			$this->errs[] = 'Error when creating thumbnail: ' . $e->getMessage();
		}
	}

	public function createMedium ($dest, $ext, $maxW=1200, $maxH=-1)
	{
		if ($maxW && $maxH>0) {
			list($maxW,$maxH) = $this->fitInRect($this->img_width, $this->img_height, $maxW, $maxH);
		}
		try {
//			$img = imagescale($this->res, $maxW, $maxH);
//			imagedestroy($this->res);
//			$this->res = $img;
//			imagejpeg($this->res, $dest.$ext, 95);
			$img = imagescale($this->res, $maxW, $maxH);
			imagejpeg($img, $dest.$ext, 95);
			imagedestroy($img);
			return filesize($dest.$ext);
		}
		catch(Exception $e) {
		//	die('Error when creating medium image: ' . $e->getMessage());
			$this->errs[] = 'Error when creating medium image: ' . $e->getMessage();
		}
	}

	public function orientImage ($dest)
	{
		$flp = 0; $rot = 0;
		$osize = filesize($this->src);
		$exif = @exif_read_data($this->src);		//file_put_contents('exif.txt', print_r($exif,true), FILE_APPEND);
		if (!$exif) return 0;
		if (!isset($exif['Orientation'])) return 0;
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
				$rot = 270;
				break;
			case 6: // 90 rotate right
				$rot = 270;
				break;
			case 7: // horizontal flip + 90 rotate right
				$flp = 1;
				$rot = 270;
				break;
			case 8: // 90 rotate left
				$rot = 90;
				break;
		}
		if (($flp + $rot) !== 0) {
			try {
				if ($flp==1) {
					$this->_flop();
				} else if ($flp==2) {
					$this->_flip();
				}
				if ($rot!==0) {
					$rimg = imagerotate($this->res, $rot, 0);
					imagedestroy($this->res);
					$this->res = $rimg;
				}
				switch ($this->img_type) {
					case 1:
						imagegif($this->res, $dest);
						break;
					case 2:
						imagejpeg($this->res, $dest, 95);
						break;
					case 3:
						imagepng($this->res, $dest, 0);
						break;
				}
				$this->img_width = imagesx($this->res);
				$this->img_height = imagesy($this->res);
				return filesize($dest) - $osize;
			}
			catch(Exception $e) {
			//	die('Error when orienting image: ' . $e->getMessage());
				$this->errs[] = 'Error when orienting image: ' . $e->getMessage();
			}
		}
		return 0;
	}

	private function resize ($nw, $nh, $cx=0, $cy=0, $cw=0, $ch=0)
	{
		$rimg = imagecreatetruecolor($nw, $nh);
		imagecopyresampled($rimg, $this->res, 0, 0, $cx, $cy, $nw, $nh, $cw?:imagesx($this->res), $ch?:imagesy($this->res));
//		imagecopyresampled($rimg, $this->res, 0, 0, $cx, $cy, $nw, $nh, $cw, $ch);
		imagedestroy($this->res);
		$this->res = $rimg;
		$this->img_width = imagesx($this->res);
		$this->img_height = imagesy($this->res);
	}

	private function _flip ()
	{
		$this->_mirror('v');
	}

	private function _flop ()
	{
		$this->_mirror('h');
	}

	private function _mirror ($how)
	{
		$width = imagesx($this->res);
		$height = imagesy($this->res);

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

		if (imagecopyresampled($new, $this->res, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height)) {
			imagedestroy($this->res);
			$this->res = $new;
		}
	}

}
