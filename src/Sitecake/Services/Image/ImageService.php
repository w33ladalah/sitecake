<?php

namespace Sitecake\Services\Image;

use Sitecake\Utils;
use Sitecake\Services\Service;
use Symfony\Component\HttpFoundation\Response;
use \WideImage\WideImage;

class ImageService extends Service {

	const SERVICE_NAME = '_image';
	
	protected static $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');

	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	protected $uploader;
	protected $imageTool;

	public function __construct($ctx) {
		$this->ctx = $ctx;
	}

	public function upload($request) {

		// obtain the uploaded file, load image and get its details (filename, extension)
		if (!$request->headers->has('x-filename')) {
			return new Response('Filename is missing (header X-FILENAME)', 400);
		}
		$filename = $request->headers->get('x-filename');

		$pathinfo = pathinfo($filename);
		if (!in_array(strtolower($pathinfo['extension']), self::$imageExtensions)) {
			return $this->json($request, array('status' => 1, 
				'errMessage' => "$filename is not an image file" ), 200);
		}

		$filename = $pathinfo['filename'];
		$ext = $pathinfo['extension'];
		$img = WideImage::load("php://input");

		// generate image set
		$res = $this->generateImageSet($img, $filename, $ext);

		$res = array(
			'status' => 0,
			'srcset' => $res['srcset'],
			'ratio' => $res['ratio']
		);

		return $this->json($request, $res, 200);
	}

	public function uploadExternal($request) {
		if (!$request->request->has('src')) {
			return new Response('Image URI is missing', 400);
		}
		$uri = $request->request->get('src');
		$referer = substr($uri, 0, strrpos($uri, '/'));
        $ch = curl_init($uri); 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $output = curl_exec($ch);
        curl_close($ch);

        try {      
			$img = WideImage::loadFromString($output);
		} catch (\Exception $e) {
			return new Response('Unable to load image from ' . $uri . ' (referer: ' . substr($uri, 0, strrchr($uri, '/')) . ')', 400);
		}
		unset($output);

		$urlinfo = parse_url($uri);
		$pathinfo = pathinfo($urlinfo['path']);
		$filename = $pathinfo['filename'];
		$ext = $pathinfo['extension'];

		// generate image set
		$res = $this->generateImageSet($img, $filename, $ext);

		$res = array(
			'status' => 0,
			'srcset' => $res['srcset'],
			'ratio' => $res['ratio']
		);

		return $this->json($request, $res, 200);
	}

	public function image($request) {
		if (!$request->request->has('image')) {
			return new Response('Image URI is missing', 400);
		}
		$uri = $request->request->get('image');

		if (!$request->request->has('data')) {
			return new Response('Image transformation data is missing', 400);
		}		
		$data = $request->request->get('data');

		if (!$this->ctx['fs']->has($uri)) {
			return new Response("Source image not found ($uri)", 400);
		}
		$img = WideImage::loadFromString($this->ctx['fs']->read($uri));

		if (Utils::isResourceUrl($uri)) {
			$info = Utils::resurlinfo($uri);
		} else {
			$pathinfo = pathinfo($uri);
			$info = array('name' => $pathinfo['filename'], 'ext' => $pathinfo['extension']);
		}

		$datas = explode(':', $data);
		$left = $datas[0];
		$top = $datas[1];
		$width = $datas[2];
		$height = $datas[3];
		$filename = $info['name'];
		$ext = $info['ext'];

		$img = $this->transform_image($img, $top, $left, $width, $height);

		// generate image set
		$res = $this->generateImageSet($img, $filename, $ext);

		$res = array(
			'status' => 0,
			'srcset' => $res['srcset'],
			'ratio' => $res['ratio']
		);

		return $this->json($request, $res, 200);
	}

	protected function generateImageSet($img, $filename, $ext) {
		$width = $img->getWidth();
		$ratio = $width/$img->getHeight();

		$widths = $this->ctx['image.srcset_widths'];
		$maxDiff = $this->ctx['image.srcset_width_maxdiff'];
		rsort($widths);

		$maxWidth = $widths[0];
		if ($width > $maxWidth) {
			$width = $maxWidth;
		}

		$id = uniqid();

		$srcset = array();
		foreach ($this->neededWidths($width, $widths, $maxDiff) as $targetWidth) {
			$tpath = Utils::resurl($this->imgDir(), $filename, $id, '-'.$targetWidth, $ext);
			$timg = $img->resize($targetWidth);
			$targetHeight = $timg->getHeight();
			$this->ctx['fs']->write($tpath, $timg->asString($ext));
			unset($timg);
			array_push($srcset, array('width' => $targetWidth, 'height' => $targetHeight, 'url' => $tpath));
		}

		return array('srcset' => $srcset, 'ratio' => $ratio);
	}

	private function neededWidths($startWidth, $widths, $maxDiff) {
		$res = array($startWidth);
		rsort($widths);
		$first = true;
		foreach ($widths as $i => $width) {
			if (!$first || ($first && ($startWidth - $width)/$startWidth > $maxDiff/100)) {
				array_push($res, $width);
				$first = false;
			}
		}
		return $res;
	}

	private function imgDir() {
		return $this->ctx['site']->draftPath().'/images';
	}

	protected function transform_image($img, $top, $left, $width, $height) {
		return $img->crop($left.'%', $top.'%', $width.'%', $height.'%');
	}


}