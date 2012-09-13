<?php
namespace sitecake;

use Zend\Http\Request as Request;
use WideImage\img as img;

class upload {
	static $forbidden = array('php', 'php5', 'php4', 'php3', 'phtml', 'phpt');
	
	static function upload_file(Request $req) {
		if(!isset($_SERVER['HTTP_X_FILENAME'])) {
			return array('status' => -1,
							'errorMessage' => 'Invalid file upload request');
		}
		
		$fileName = $_SERVER['HTTP_X_FILENAME'];
		$comps = explode(".", $fileName);
		$fileExt = $comps[count($comps)-1];

		if (in_array(strtolower($fileExt), self::$forbidden) ) {
			return array('status' => -1, 
				'errorMessage' => 'Not allowed file type');
		}
		
		$id = util::id();
		$file = DRAFT_CONTENT_DIR . '/' . $id . 
			($fileExt ? '.' . $fileExt : '');
			
		io::file_put_contents($file, io::file_get_contents("php://input"));
		meta::put($id, array(
			'path' => util::rpath($file),
			'name' => basename($file), 
			'image' => isset($_SERVER['HTTP_X_IMAGE'])
		));

		$result = array('status' => 0);
		$result['id'] = $id;
		$result['url'] = DRAFT_CONTENT_URL . '/' . 
			$id .	($fileExt ? '.' . $fileExt : '');
				
		if (isset($_SERVER['HTTP_X_IMAGE']) && 
				isset($_SERVER['HTTP_X_RESIZE_WIDTH']) && 
				$_SERVER['HTTP_X_RESIZE_WIDTH'] != 0 ) {
			$resizedWidth = $_SERVER['HTTP_X_RESIZE_WIDTH'];
			$resizedId = util::id();
			$resizedFile = DRAFT_CONTENT_DIR . '/' .
				$resizedId .	($fileExt ? '.' . $fileExt : '');
			img::load($file);
			if (img::getWidth() <= $resizedWidth) {
				io::copy($file, $resizedFile);
			} else {
				img::resizeToWidth($resizedWidth);
				img::save($resizedFile);
			}
			$result['resizedUrl'] = DRAFT_CONTENT_URL . '/' . 
				$resizedId .	($fileExt ? '.' . $fileExt : '');
			$result['resizedWidth'] = img::getWidth();
			$result['resizedHeight'] = img::getHeight();
			img::unload();
			meta::put($resizedId, array(
				'orig' => util::rpath($file),
				'oid'  => $id,
				'path' => util::rpath($resizedFile),
				'name' => basename($resizedFile),
				'image' => true
			));				
		}		
		
		return $result;
	}
}