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
		$this->uploader = new Upload($ctx['fs'], $ctx['site']->draftPath());
		$this->imageTool = new ImageTool($ctx['fs'], $ctx['site']->draftPath().'/images');
	}

	public function upload($request) {
		if (!$request->headers->has('x-filename')) {
			return new Response('Filename is missing (header X-FILENAME)', 400);
		}
		$filename = $request->headers->get('x-filename');
		$url = $this->uploader->save($filename);
		if ($url === false) {
			return $this->json($request, array('status' => 1, 'errMessage' => 'Unable to upload image'), 200);
		}

		$info = Utils::resurlinfo($url);

		$res = array(
			'status' => 0,
			'url' => $url,
			'id' => $info['id']
		);

		$resizedWidth = $request->headers->get('x-resize-width', 0);
		if ( $resizedWidth > 0) {
			$img = WideImage::loadFromString($this->ctx['fs']->read($url));
			$img->resizeDown($resizedWidth);
			$resizedUrl = $this->ctx['site']->draftPath().'/images/'.$info['name'].'-sc'.$info['id'].'01.'.$info['ext'];
			$this->ctx['fs']->write($resizedUrl, $img->asString($info['ext']));
			$res['resizedUrl'] = $resizedUrl;
			$res['resizedWidth'] = $img->getWidth();
			$res['resizedHeight'] = $img->getHeight();
		}

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
			return new Response('Source image not found', 400);
		}

		$info = Utils::resurlinfo($uri);
		$newUri = $this->ctx['site']->draftPath().'/images/'.$info['name'].'-sc'.uniqid().'.'.$info['ext'];
		$this->transform_image($uri, $newUri, $data);

		return $this->json($request, array('status' => 0, 'url' => $newUri), 200);
	}

	protected function transform_image($spath, $dpath, $data) {
		$datas = explode(':', $data);
		$srcWidth = $datas[0];
		$srcHeight = $datas[1];
		$srcX = $datas[2];
		$srcY = $datas[3];
		$dstWidth = $datas[4];
		$dstHeight = $datas[5];
		
		$img = WideImage::loadFromString($this->ctx['fs']->read($spath));
			
		$origWidth = $img->getWidth();
		$origHeight = $img->getHeight();
		
		$xRatio = $origWidth / $srcWidth;
		$yRatio = $origHeight / $srcHeight;
		
		$srcWidth = $dstWidth * $xRatio;
		$srcHeight= $dstHeight * $yRatio;
		$srcX = $srcX * $xRatio;
		$srcY = $srcY * $yRatio;
		
		$img = $this->transform($img, $srcX, $srcY, $srcWidth, $srcHeight, 
			$dstWidth, $dstHeight);
		$info = Utils::resurlinfo($dpath);
		$this->ctx['fs']->write($dpath, $img->asString($info['ext']));
	}

	protected function transform($img, $sx, $sy, $swidth, $sheight, $dwidth, $dheight) {
		if ($dwidth == null) {
			$dwidth = $img->getWidth();
		}
	
		if ($dheight == null) {
			$dheight = $img->getHeight();
		}
	
		return $img->crop($sx, $sy, $swidth, $sheight)->resize($dwidth, $dheight);
	}	

}