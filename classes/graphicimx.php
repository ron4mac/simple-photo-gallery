<?php
include_once 'imgproc.php';

function _log($d) {file_put_contents('LOG.txt',print_r($d,true)."\n",FILE_APPEND);}

class ImageProcessor extends ImageProc
{
	public $ipp = 'IMX';
	protected $errs = [];
	protected $src;
	protected $imgk;

	public function __construct ($src)
	{
		parent::__construct($src);

		try {
			$this->src = $src;
			$this->imgk = new Imagick(realpath($src));
		//	$this->imgk->readImage($src);
		}
		catch(Exception $e) {
		//	die('Error getting image: ' . $e->getMessage());
			$this->errs[] = 'Error getting image: ' . $e->getMessage();
		}
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
			$this->imgk->scaleImage($nw, $nh);
			break;
		case 'fill':
			$geo = $this->imgk->getImageGeometry();
		//	list($nw, $nh) = $this->fillInRect($this->img_width, $this->img_height, $w, $h);
			list($nw, $nh) = $this->fillInRect($geo['width'], $geo['height'], $w, $h);
			$wr = $geo['width']/$nw;
			$hr = $geo['height']/$nh;
			$sw = (int)($w*$wr);
			$sh = (int)($h*$hr);
			$cx = $sw-$w;
			$cy = $sh-$h;
			$this->imgk->scaleImage($sw, $sh);
			$this->imgk->cropImage($w, $h, $cx>>1, $cy>>1);
			break;
		}
		$this->imgk->writeImage($dest);
		return filesize($dest);
	}

	public function createThumb ($dest, $ext, $maxW=0, $maxH=100, $sqr=true)
	{
		try {
			$this->imgk->cropThumbnailImage($maxW, $maxH);
			$this->imgk->setImageFormat('JPG');
			$this->imgk->writeImage($dest.$ext);
			return filesize($dest.$ext);
		}
		catch(Exception $e) {
		//	die('Error when creating a thumbnail: ' . $e->getMessage());
			$this->errs[] = 'Error when creating thumbnail: ' . $e->getMessage();
		}
	}

	public function createMedium ($dest, $ext, $maxW=0, $maxH=1200)
	{
		try {
			$this->imgk->scaleImage($maxW, $maxH, (bool)($maxW && $maxH));
			$this->imgk->writeImage($dest.$ext);
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
		$osize = filesize(realpath($this->src));
		$exif = @exif_read_data(realpath($this->src));		//file_put_contents('exif.txt', print_r($exif,true), FILE_APPEND);
		if (!$exif) return;
		$ort = $exif['Orientation'] ?? 0;
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
				$rot = 90;
				break;
			case 6: // 90 rotate right
				$rot = 90;
				break;
			case 7: // horizontal flip + 90 rotate right
				$flp = 1;
				$rot = 90;
				break;
			case 8: // 90 rotate left
				$rot = -90;
				break;
		}
		if (($flp + $rot) !== 0) {
			try {
				if ($flp==1) { $this->imgk->flipImage(); }
				else if ($flp==2) { $this->imgk->flopImage(); }
				if ($rot!==0) { $this->imgk->rotateImage(new ImagickPixel('#00000000'), $rot); }
				$this->imgk->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
				$this->imgk->writeImage(realpath($dest));
				return filesize(realpath($dest)) - $osize;
			}
			catch(Exception $e) {
			//	die('Error when orienting image: ' . $e->getMessage());
				$this->errs[] = 'Error when orienting image: ' . $e->getMessage();
			}
		}
		return 0;
	}

}
