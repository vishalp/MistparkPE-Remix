<?php

if(! class_exists("Photo")) {
class Photo {

	private $image;
	private $width;
	private $height;

	public function __construct($data) {
		$this->image = @imagecreatefromstring($data);
		if($this->image !== FALSE) {
			$this->width  = imagesx($this->image);
			$this->height = imagesy($this->image);
		}
	}

	public function __destruct() {
		if($this->image)
			imagedestroy($this->image);
	}

	public function getWidth() {
		return $this->width;
	}

	public function getHeight() {
		return $this->height;
	}

	public function getImage() {
		return $this->image;
	}

	public function scaleImage($max) {

		$width = $this->width;
		$height = $this->height;

		$dest_width = $dest_height = 0;

		if((! $width)|| (! $height))
			return FALSE;

		if($width > $max && $height > $max) {
			if($width > $height) {
				$dest_width = $max;
				$dest_height = intval(( $height * $max ) / $width);
			}
			else {
				$dest_width = intval(( $width * $max ) / $height);
				$dest_height = $max;
			}
		}
		else {
			if( $width > $max ) {
				$dest_width = $max;
				$dest_height = intval(( $height * $max ) / $width);
			}
			else {
				if( $height > $max ) {
					$dest_width = intval(( $width * $max ) / $height);
					$dest_height = $max;
				}
				else {
					$dest_width = $width;
					$dest_height = $height;
				}
			}
		}


		$dest = imagecreatetruecolor( $dest_width, $dest_height );
		imagecopyresampled($dest, $this->image, 0, 0, 0, 0, $dest_width, $dest_height, $width, $height);
		if($this->image)
			imagedestroy($this->image);
		$this->image = $dest;
		$this->width  = imagesx($this->image);
		$this->height = imagesy($this->image);

	}



	public function scaleImageUp($min) {

		$width = $this->width;
		$height = $this->height;

		$dest_width = $dest_height = 0;

		if((! $width)|| (! $height))
			return FALSE;

		if($width < $min && $height < $min) {
			if($width > $height) {
				$dest_width = $min;
				$dest_height = intval(( $height * $min ) / $width);
			}
			else {
				$dest_width = intval(( $width * $min ) / $height);
				$dest_height = $min;
			}
		}
		else {
			if( $width < $min ) {
				$dest_width = $min;
				$dest_height = intval(( $height * $min ) / $width);
			}
			else {
				if( $height < $min ) {
					$dest_width = intval(( $width * $min ) / $height);
					$dest_height = $min;
				}
				else {
					$dest_width = $width;
					$dest_height = $height;
				}
			}
		}


		$dest = imagecreatetruecolor( $dest_width, $dest_height );
		imagecopyresampled($dest, $this->image, 0, 0, 0, 0, $dest_width, $dest_height, $width, $height);
		if($this->image)
			imagedestroy($this->image);
		$this->image = $dest;
		$this->width  = imagesx($this->image);
		$this->height = imagesy($this->image);

	}



	public function scaleImageSquare($dim) {

		$dest = imagecreatetruecolor( $dim, $dim );
		imagecopyresampled($dest, $this->image, 0, 0, 0, 0, $dim, $dim, $this->width, $this->height);
		if($this->image)
			imagedestroy($this->image);
		$this->image = $dest;
		$this->width  = imagesx($this->image);
		$this->height = imagesy($this->image);
	}


	public function cropImage($max,$x,$y,$w,$h) {
		$dest = imagecreatetruecolor( $max, $max );
		imagecopyresampled($dest, $this->image, 0, 0, $x, $y, $max, $max, $w, $h);
		if($this->image)
			imagedestroy($this->image);
		$this->image = $dest;
		$this->width  = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	public function saveImage($path) {
		imagejpeg($this->image,$path,100);
	}

	public function imageString() {
		ob_start();
		imagejpeg($this->image,NULL,100);
		$s = ob_get_contents();
		ob_end_clean();
		return $s;
	}



	public function store($cid, $rid, $filename, $album, $scale, 
		$profile = 0, $allow_cid = '', $allow_gid = '', $deny_cid = '', $deny_gid = '') {

		$r = q("INSERT INTO `photo`
			( `contact-id`, `resource-id`, `created`, `edited`, `filename`, `album`, `height`, `width`, `data`, `scale`, `profile`, `allow_cid`, `allow_gid`, `deny_cid`, `deny_gid` )
			VALUES ( %d, '%s', '%s', '%s', '%s', '%s', %d, %d, '%s', %d, %d, '%s', '%s', '%s', '%s' )",
			intval($cid),
			dbesc($rid),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			dbesc(basename($filename)),
			dbesc($album),
                	intval($this->height),
                	intval($this->width),
                	dbesc($this->imageString()),
			intval($scale),
			intval($profile),
			dbesc($allow_cid),
			dbesc($allow_gid),
			dbesc($deny_cid),
			dbesc($deny_gid)
		);
		return $r;
	}





}}


