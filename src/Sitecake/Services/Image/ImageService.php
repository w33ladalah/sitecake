<?php

namespace Sitecake\Services\Image;

use Sitecake\Utils;
use Sitecake\Services\Service;
use Sitecake\Services\Upload\Upload;
use Symfony\Component\HttpFoundation\Response;
use \WideImage\WideImage;

class ImageService extends Service {

	const SERVICE_NAME = '_image';
	
	public static function name() {
		return self::SERVICE_NAME;
	}

	protected $ctx;

	protected $uploader;
	protected $imageTool;

	public function __construct($ctx) {
		$this->ctx = $ctx;
		$this->uploader = new Upload($ctx['fs']);
		$this->imageTool = new ImageTool($ctx['fs'], $ctx['site']->draftPath().'/images');
	}

	public function upload($request) {
		if (!$request->headers->has('x-filename')) {
			return new Response('Filename is missing (header X-FILENAME)', 400);
		}
		$filename = $request->headers->get('x-filename');

		$pathinfo = pathinfo($filename);
		$ext = $pathinfo['extension'];
		$dpath = Utils::resurl($this->ctx['site']->draftPath().'/images', 
			$pathinfo['filename'], null, null, $pathinfo['extension']);
		$url = $this->uploader->save($dpath);
		if ($url === false) {
			return $this->json($request, array('status' => 1, 'errMessage' => 'Unable to upload image'), 200);
		}

		$info = Utils::resurlinfo($url);

		$res = array(
			'status' => 0,
			'url' => $url,
			'src' => $url
		);

		$resizedWidth = $request->headers->get('x-resize-width', 0);

		$img = WideImage::loadFromString($this->ctx['fs']->read($url));
		$origWidth = $img->getWidth();
		$origHeight = $img->getHeight();

		$set = $this->srcset($this->ctx['site']->draftPath().'/images', $pathinfo['filename'], $pathinfo['extension'], uniqid());
		$srcset = array($url.' '.$origWidth.'w');
		foreach ($set as $item) {
			if ((abs($origWidth - $item['width']) > 100) && (($origWidth - 100) > $item['width'])) {
				$timg = $img->copy()->resize($item['width']);
				$this->ctx['fs']->write($item['path'], $timg->asString($ext));
				unset($timg);
				array_push($srcset, $item['path'].' '.$item['width'].'w');
			}
		}
		$res['srcset'] = implode(',', $srcset);
		$res['sizes'] = "";
		$res['width'] = $resizedWidth;
		$res['height'] = (string)intval($resizedWidth*$origHeight/$origWidth);

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

		$newId = uniqid();
		$newUri = $this->ctx['site']->draftPath().'/images/'.$info['name'].'-sc'.$newId.'.'.$info['ext'];
		
		$res = array(
			'status' => 0,
			'src' => $newUri
		);		

		$datas = explode(':', $data);
		$left = $datas[0];
		$top = $datas[1];
		$width = $datas[2];
		$height = $datas[3];

		$img = $this->transform_image($img, $top, $left, $width, $height);
		$origWidth = $img->getWidth();
		$origHeight = $img->getHeight();

		$this->ctx['fs']->write($newUri, $img->asString($info['ext']));

		$set = $this->srcset($this->ctx['site']->draftPath().'/images', $info['name'], $info['ext'], uniqid());
		$srcset = array($newUri.' '.$origWidth.'w');
		foreach ($set as $item) {
			if ((abs($origWidth - $item['width']) > 100) && (($origWidth - 100) > $item['width'])) {
				$timg = $img->copy()->resize($item['width']);
				$this->ctx['fs']->write($item['path'], $timg->asString($info['ext']));
				unset($timg);
				array_push($srcset, $item['path'].' '.$item['width'].'w');
			}
		}
		$res['srcset'] = implode(',', $srcset);
		$res['sizes'] = "";

		return $this->json($request, $res, 200);
	}

	private function srcset($dir, $name, $ext, $id) {
		return array(
			array('width' => 1280, 'path' => Utils::resurl($dir, $name, $id, '-1280w', $ext)),
			array('width' => 960, 'path' => Utils::resurl($dir, $name, $id, '-960w', $ext)),
			array('width' => 640, 'path' => Utils::resurl($dir, $name, $id, '-640w', $ext)),
			array('width' => 320, 'path' => Utils::resurl($dir, $name, $id, '-320w', $ext))
		);
	}

	private function defsrc($containerWidth, $url, $srcset) {
		$max = 0;
		$diff = array_map(function($item) {
				return $item['width'] - $containerWidth;
			}, $srcset);
		foreach ($srcset as $item) {

		}
	}

	protected function transform_image($img, $top, $left, $width, $height) {
		return $img->crop($left.'%', $top.'%', $width.'%', $height.'%');
	}


}