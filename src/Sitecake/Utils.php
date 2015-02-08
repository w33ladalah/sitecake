<?php
namespace Sitecake;

use Sitecake\HtmlUtils;

class Utils {
	
	/**
	 * Generates unique identifier.
	 * @return string
	 */
	static function id() {
		return sha1(uniqid('', true));
	}

	static function map($callback, $arr1, $_ = null) {
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		$res = array();
		$idx = 0;
		foreach ($arr1 as $el) {
			$params = array($el);
			foreach ($args as $arg) {
				array_push($params, $arg[$idx]);
			}
			array_push($res, call_user_func_array($callback, $params));
			$idx++;
		}
		return $res;
	}
	
	static function array_map_prop($array, $property) {
		return array_map(function($el) use ($property) {
				return $el[$property]; 
			}, $array);
	}
	
	static function array_filter_prop($array, $property, $value) {
		return array_filter($array, function($el) use ($property, $value) {
				return isset($el[$property]) ? 
					($el[$property] == $value) : false;
			});
	}
	
	static function array_find_prop($array, $prop, $value) {
		return array_shift(Utils::array_filter_prop($array, $prop, $value));
	}
	
	static function array_diff($arr1, $arr2) {
		$res = array_diff($arr1, $arr2);
		return is_array($res) ? $res : array();
	}
	
	static function iterable_to_array($iterable) {
		$res = array();
		foreach ($iterable as $item) {
			array_push($res, $item);
		}
		return $res;
	}

	static function str_endswith($needle, $haystack) {
		$len = strlen($needle);
		return ($len > strlen($haystack)) ? false : 
			(substr($haystack, -$len) === $needle);
	}

	public static function isURL($uri) {
		return (preg_match('/^https?:\/\/.*$/', $uri) === 1);
	}

	public static function nameFromURL($url) {
		$path = parse_url($url, PHP_URL_PATH);
		$dot = strrpos($path, '.');
		if ($dot !== false) {
			$path = substr($path, 0, $dot);
		}
		if (strpos($path, '/') === 0) {
			$path = substr($path, 1);
		}
		return preg_replace('/[^0-9a-zA-Z\.\-_]+/', '-', $path);
	}

	/**
	 * Create a web friendly URL slug from a string.
	 * 
	 * Although supported, transliteration is discouraged because
	 *     1) most web browsers support UTF-8 characters in URLs
	 *     2) transliteration causes a loss of information
	 *
	 * @author Sean Murphy <sean@iamseanmurphy.com>
	 * @copyright Copyright 2012 Sean Murphy. All rights reserved.
	 * @license http://creativecommons.org/publicdomain/zero/1.0/
	 *
	 * @param string $str
	 * @param array $options
	 * @return string
	 */
	static function slug($str, $options = array()) {
		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
		$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
		
		$defaults = array(
			'delimiter' => '-',
			'limit' => null,
			'lowercase' => true,
			'replacements' => array(),
			'transliterate' => false,
		);
		
		// Merge options
		$options = array_merge($defaults, $options);
		
		$char_map = array(
			// Latin
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
			'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
			'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
			'ß' => 'ss', 
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
			'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
			'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
			'ÿ' => 'y',

			// Latin symbols
			'©' => '(c)',

			// Greek
			'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
			'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
			'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			'Ϋ' => 'Y',
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

			// Turkish
			'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
			'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 

			// Russian
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
			'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
			'Я' => 'Ya',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',

			// Ukrainian
			'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
			'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

			// Czech
			'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
			'Ž' => 'Z', 
			'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
			'ž' => 'z', 

			// Polish
			'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
			'Ż' => 'Z', 
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			'ż' => 'z',

			// Latvian
			'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
			'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
			'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
			'š' => 's', 'ū' => 'u', 'ž' => 'z'
		);
		
		// Make custom replacements
		$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
		
		// Transliterate characters to ASCII
		if ($options['transliterate']) {
			$str = str_replace(array_keys($char_map), $char_map, $str);
		}
		
		// Replace non-alphanumeric characters with our delimiter
		$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
		
		// Remove duplicate delimiters
		$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
		
		// Truncate slug to max. characters
		$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
		
		// Remove delimiter from ends
		$str = trim($str, $options['delimiter']);
		
		return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
	}

	/**
	 * Creates a resource URL out of the given components.
	 *
	 * @param  string $path  resource path prefix (directory) or full resource path (dir, name, ext)
	 * @param  string $name  resource name
	 * @param  string $id    13-digit resource ID (uniqid)
	 * @param  string $subid resource additional id (classifier, subid)
	 * @param  string $ext   extension
	 * @return string        calculated resource path
	 */
	public static function resurl($path, $name = null, $id = null, $subid = null, $ext = null) {
		$id = ($id == null) ? uniqid() : $id;
		$subid = ($subid == null) ? '' : $subid;
		if ($name == null || $ext == null) {
			$pathinfo = pathinfo($path);
			$name = ($name == null) ? $pathinfo['filename'] : $name; 
			$ext = ($ext == null) ? $pathinfo['extension'] : $ext;
			$path = ($pathinfo['dirname'] === '.') ? '' : $pathinfo['dirname'];
		}
		$path = $path . (($path === '' || substr($path, -1) === '/') ? '' : '/');
		$name = str_replace(' ', '_', $name);
		$ext = strtolower($ext);
		return $path.$name.'-sc'.$id.$subid.'.'.$ext;
	} 

	/**
	 * Checks if the given URL is a Sitecake resource URL.
	 * 
	 * @param  string  $url a URL to be tested
	 * @return boolean      true if the URL is a Sitecake resource URL
	 */
	public static function isResourceUrl($url) {
		$re = '/^.*(files|images)\/.*\-sc[0-9a-f]{13}[^\.]*\..+$/';

		return HtmlUtils::isRelativeURL($url) &&
				preg_match($re, $url) &&
				(strpos($url, 'javascript:') !== 0) &&
				(strpos($url, '#') !== 0);
	}

	private static function _resurlinfo($url) {
		preg_match('/((.*)\/)?([^\/]+)-sc([0-9a-fA-F]{13})([^\.]*)\.([^\.]+)$/', $url, $match);
		return array(
			'path' => $match[2],
			'name' => $match[3],
			'id' => $match[4],
			'subid' => $match[5],
			'ext' => $match[6]
		);
	}

	/**
	 * Extracts information from a resource URL.
	 * It returns path, name, id, subid and extension.
	 * 
	 * @param  string|array $url a URL to be deconstructed or a list of URLs
	 * @return array      URL components (path, name, id, subid, ext) or a list of URL components
	 */
	public static function resurlinfo($urls) {
		if (is_array($urls)) {
			$res = array();
			foreach ($urls as $url) {
				array_push($res, self::_resurlinfo($url));
			}
			return $res;
		} else {
			return self::_resurlinfo($urls);
		}
	}	
	
	/**
	 * Checks if the given URL is a URL to a resource that is not a local HTML page.
	 * 
	 * @param  string  $url URL to be checked
	 * @return boolean true if the link is a URL to a resource that is not a local HTML page
	 */
	public static function isExternalNavLink($url) {
		return (strpos($url, '/') !== false) || (strpos($url, 'http://') === 0) || 
			(substr($url, -5) !== '.html');
	}			

}