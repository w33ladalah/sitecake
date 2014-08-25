<?php
namespace sitecake;

use \phpQuery as phpQuery;
use \phpQuery\DOMDocumentWrapper as DOMDocumentWrapper;
use \wideimage\img as img;
use \Zend\Json\Json as json;

class image {
	
	static function transform($params) {
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
	
}