<?php

namespace Sitecake;

use \WideImage\WideImage;

class ImageTool {
	
	static protected $mimeExtensions = array(
		'jpg' => 'image/jpg', 
		'jpg' => 'image/jpeg', 
		'jpg' => 'image/pjpeg', 
		'gif' => 'image/gif', 
		'png' => 'image/png'
	);

	protected $fs;

	protected $imagesDir;

	public function __construct($fs, $imagesDir) {
		$this->fs = $fs;
		$this->imagesDir = $imagesDir;
	}

	public function image($uri, $x = '0%', $y = '0%', $w = '100%', $h = '100%') {
		if (Utils::isURL($uri)) {
			list($image, $extension) = $this->loadFromURL($uri);
			//$this->transform(Utils::nameFromURL($uri), $extension, WideImage::loadFromString($image), $x, $y, $w, $h) :
		} else {
			$imageName = $this->imageName($uri);
			$this->transform($imageName['name'], $imageName['extension'], WideImage::loadFromString($this->fs->read($uri)), $x, $y, $w, $h);
		}
	}

	protected function parseImgPathSet($id) {
		$pathPattern = 
		$this->fs->listPatternPaths($pathPattern);
	}

	protected function imageName($uri) {
		$info = pathinfo($uri);
		return array('name' => $info['filename'], 'extension' => $info['extension']);
	}

	protected function loadFromURL($url) {
		$image = file_get_contents($url);
  		$headers = http_parse_headers(implode("\r\n", $http_response_header));
  		$ext = array_search($headers['Content-Type'], $mimeExtensions);
		return array($image, $ext);
	}

	protected function transform($name, $extension, $img, $x, $y, $w, $h) {
		$origWidth = $img->getWidth();

		$img->crop($x, $y, $w, $h);
	}

	static function transform1($params) {
		$url = $params['image'];	
		$data = $params['data'];
		$info = image::image_info($url);
		$path = $info['path'];
		
		if (!io::file_exists($path))
			throw new \Exception(resources::message('FILE_NOT_EXISTS', $path));
	
		if (meta::exists($info['id'])) {
			$meta = meta::get($info['id']);
			$spath = util::apath($meta['orig']);
			$data = isset($meta['data']) ? 
				image::combine_transform($meta['data'], $data) : $data;
		} else {
			$spath = $path;
		}
	
		$id = util::id();
		$name = $id . '.' . $info['ext'];
		$dpath = DRAFT_CONTENT_DIR . '/' . $name;
		image::transform_image($spath, $dpath, $data);
		meta::put($id, array(
							'orig' => util::rpath($spath),
							'oid'  => $info['id'],
							'path' => util::rpath($dpath),
							'name' => $name,
							'data' => $data,
							'image' => true
		));
		return array('status' => 0, 
			'url' => DRAFT_CONTENT_URL . '/' . $name);
	}
	
	static function image_info($url) {
		return array(
				'id' => reset(explode('.', end(explode('/', $url)))),
				'ext' => end(explode('.', end(explode('/', $url)))),
				'path' => SC_ROOT . '/' . $url,
				'name' => basename(SC_ROOT . '/' . $url)
		);
	}
	
	static function combine_transform($old, $new) {
		list($osw, $osh, $osx, $osy, $odw, $odh) = explode(':', $old);
		list($sw, $sh, $sx, $sy, $dw, $dh) = explode(':', $new);
		$dx = $sw/$odw;
		$dy = $sh/$odh;
		
		return implode(':', array($dx*$osw, $dy*$osh, 
			$dx*$osx + abs($sx), $dy*$osy + abs($sy), $dw, $dh));
	}
	
	static function transform_image($spath, $dpath, $data) {
		$datas = explode(':', $data);
		$srcWidth = $datas[0];
		$srcHeight = $datas[1];
		$srcX = $datas[2];
		$srcY = $datas[3];
		$dstWidth = $datas[4];
		$dstHeight = $datas[5];
		
		img::load($spath);
			
		$origWidth = img::getWidth();
		$origHeight = img::getHeight();
		
		$xRatio = $origWidth / $srcWidth;
		$yRatio = $origHeight / $srcHeight;
		
		$srcWidth = $dstWidth * $xRatio;
		$srcHeight= $dstHeight * $yRatio;
		$srcX = $srcX * $xRatio;
		$srcY = $srcY * $yRatio;
		
		img::transform($srcX, $srcY, $srcWidth, $srcHeight, 
			$dstWidth, $dstHeight);
		img::save($dpath);
		img::unload();
	}
	
	static function resizeToHeight($height) {
		self::$image = self::$image->resize(null, $height);
	}
	 
	static function resizeToWidth($width) {
		self::$image = self::$image->resize($width,null);
	}
	
	static function resizeToDimension($dimension) {
		if (self::$image->getWidth() >= self::$image->getHeight()) {
			self::resizeToWidth($dimension);
		} else {
			self::resizeToHeight($dimension);
		}
	}
	 
	static function scale($scale) {
		$width = self::getWidth() * $scale/100;
		$height = self::getHeight() * $scale/100;
		self::$image = self::$image->resize($width, $height);
	}
	 
	static function resize($width, $height) {
		self::$image = self::$image->resize( $width, $height );
	}
	 
	static function _transform($sx, $sy, $swidth, $sheight, $dwidth, $dheight) {
		if ($dwidth == null) {
			$dwidth = self::getWidth();
		}
	
		if ($dheight == null) {
			$dheight = self::getHeight();
		}
	
		self::$image = self::$image->crop($sx, $sy, $swidth, $sheight)->
			resize($dwidth, $dheight);
	}	
}