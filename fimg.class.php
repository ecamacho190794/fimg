<?php

/**
 * @author Erik Camacho
 * @version 1.0.2
 * @web fassem.com
 * @email erik190794@gmail.com
 * 
 * 1.0.2
 * Fully Translated to english
 * Created FIMGException class
 *
 * 1.0.1
 * Added compatibility with PHP 4.4.x
 *
 */


/**
 * Format type constants.
 */
define('FJPEG', 'image/jpeg');
define('FJPG', 'image/jpg');
define('FPNG', 'image/png');
define('FGIF', 'image/gif');

/**
 * Exception class
 * @version 1.0
 *
 * Class to throw Exceptions. Now it is empty
 *
 */
class FIMGException extends Exception { }

class FIMG {

	private $image;
	private $width;
	private $height;
	private $type;
	private $path;
	private $newImage;
	private $newWidth;
	private $newHeight;

	function __construct($path) {
		if (!is_string($path)){
			throw new FIMGException("First argument should be string");
		}
		if ($path == ""){
			throw new FIMGException("Invalid path");
		}
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		if (!$mime) {
			throw new FIMGException("Invalid file name: ".$mime);
		}
		switch ($mime) {
	        case 'image/jpg':
	            $this->type = FJPG;
	            $this->image = imagecreatefromjpeg($path);
	            break;
	        case 'image/jpeg':
	            $this->type = FJPEG;
	            $this->image = imagecreatefromjpeg($path);
	            break;
	        case 'image/png':
	            $this->type = FPNG;
	            $this->image = imagecreatefrompng($path);
	            break;
	        case 'image/gif':
	            $this->type = FGIF;
	            $this->image = imagecreatefromgif($path);
	            break;
	        default:
	            throw new FIMGException("File is not an image");
	    }
	    $this->path = $path;
	    $is = getimagesize($path);
	    $this->newWidth = $this->width = $is[0];
	    $this->newHeight = $this->height = $is[1];
	    $this->newImage = null;
	}

	private function resize(){
		if (version_compare(PHP_VERSION, '5.5.0', '<') ) {
			$this->newImage = imagecreatetruecolor($this->newWidth, $this->newHeight);
			imagecopyresized($this->newImage, $this->image, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
		}else{
			$this->newImage = imagescale($this->image, $this->newWidth, $this->newHeight, IMG_BICUBIC_FIXED);
		}
	}

	public function size($width = null, $height = null) {
		if ($width == null && $height == null){
			return array($this->newWidth, $this->newHeight);
		}
		if ($width == null){
			if (!is_integer($height)) {
				throw new FIMGException("Height parameter should be an integer");
			}
			$this->newHeight = $height;
		}elseif ($height == null){
			if (!is_integer($width)) {
				throw new FIMGException("Width parameter should be an integer");
			}
			$this->newWidth = $width;
		}else {
			if (!is_integer($width) || !is_integer($height)){
				throw new FIMGException("Parameters should be Integer");
			}
			$this->newWidth = $width;
			$this->newHeight = $height;
		}
	}

	public function setWidth($width, $resize=true){
		if($resize){
			$this->newHeight = $width * $this->height / $this->width;
			$this->newWidth = $width;
		} else {
			$this->newWidth = $width;
		}
	}

	public function setHeight($height, $resize=true){
		if($resize){
			$this->newWidth = $height * $this->width / $this->height;
			$this->newHeight = $height;
		}else{
			$this->newHeight = $height;
		}
	}

	public function save($path=null, $type=null){
		if (!is_string($path) && $path!=null) {
			throw new FIMGException("First argument should be a string");
		}
		$checkType = $type == null ? $this->type : $type;
		$this->resize();
		switch ($checkType) {
			case FJPG:
	        case FJPEG:
	            if (isset($path)) {
					imagejpeg($this->newImage, $path);
				} else {
					imagejpeg($this->newImage, $this->path);
				}
	            break;
	        case FPNG:
	            if (isset($path)) {
					imagepng($this->newImage, $path);
				} else {
					imagepng($this->newImage, $this->path);
				}
	            break;
	        case FGIF:
	            if (isset($path)) {
					imagegif($this->newImage, $path);
				} else {
					imagegif($this->newImage, $this->path);
				}
	            break;
	    }
	}

	public function show() {
		header("content-type: ".$this->type);
		$this->resize();
		switch ($this->type) {
			case FJPG:
	        case FJPEG:
				imagejpeg($this->newImage);
				break;
	        case FPNG:
				imagepng($this->newImage);
	            break;
	        case FGIF:
				imagegif($this->newImage);
	            break;
	    }
	}

	public function delete() {
		unlink($this->path);
	}

	public function close() {
		imagedestroy($this->image);
		if ($this->newImage == null){
			return;
		}
		imagedestroy($this->newImage);
	}

	public function getType() {
		return $this->type;
	}

	public function getFormat() {
		return $this->getType();
	}

	public function getImage() {
		return $this->image;
	}

	public function getPath() {
		return $this->path;
	}

	public static function getUniqueName($extension = 'jpg'){
		switch ($extension) {
			case FJPG:
	        case FJPEG:
	            $extension = 'jpg';
	            break;
	        case FPNG:
	            $extension = 'png';
	            break;
	        case FGIF:
	            $extension = 'gif';
	            break;
		}
		date_default_timezone_set('UTC');
		$name = "img_";
		$name.= date("YmdHis");
		$name.= substr(md5(rand(0, PHP_INT_MAX)), 10);
		$name.= ".".$extension;
		return $name;
	}
}

?>