<?php

namespace Sitecake;

use \phpQuery;

class HtmlUtils {

	/**
	 * Append the give HTML code to the HTML page head section.
	 * 
	 * @param  string|phpQueryObject $html  html page or a phpQueryObject
	 * @param  [type] $code [description]
	 * @return [type]       [description]
	 */
	public static function appendToHead($html, $code) {
		$doc = HtmlUtils::toDoc($html);
		phpQuery::pq('head', $doc)->append($code);
		return $doc;
	}

	/**
	 * Wraps the given JavaScript code with a <script> tag.
	 * 
	 * @param  string $code JavaScript code to be wrapped
	 * @return string       the wrapped code
	 */
	static function wrapToScriptTag($code) {
		return '<script type="text/javascript">' . $code . '</script>';
	}
	
	/**
	 * Returns a <script> html tag for loading JavaScript code from the given URL.
	 * 
	 * @param  string $url the tag src attribute
	 * @return string      the result script tag
	 */
	static function scriptTag($url) {
		return '<script type="text/javascript" language="javascript" src="' .
			$url . '"></script>';	
	}
	
	/**
	 * Returns a phpQueryObject document created of the given HTML code. 
	 * @param  string $html HTML code
	 * @return phpQueryObject	the resulting phpQueryObject instance
	 */
	public static function strToDoc($html) {
		return phpQuery::newDocument($html);
	}

	/**
	 * Converts the given HTML document into a phpQueryObject document if not
	 * already.
	 * 
	 * @param  string|phpQueryObject $obj the input HTML document. It could be a HTML string or already
	 *                                    a phpQueryObject
	 * @return phpQueryObject      the resulting HTML document 
	 */
	public static function toDoc($obj) {
		return is_object($obj) ? $obj : HtmlUtils::strToDoc($obj);
	}

	/**
	 * Tests if the given URL is an absolute URL.
	 * 
	 * @param  string  $url an URL to be tested
	 * @return boolean      the test result
	 */
	public static function isAbsoluteURL($url) {
		return (strpos($url, 'http://') === 0) || (strpos($url, 'https://') === 0);
	}

	/**
	 * Tests if the given URL is an relative URL.
	 * 
	 * @param  string  $url an URL to be tested
	 * @return boolean      the test result
	 */
	public static function isRelativeURL($url) {
		return !((strpos($url, 'http://') === 0) || (strpos($url, 'https://') === 0));
	}

	/**
	 * Prefix the given node's attribute with a prefix if its value satisfies
	 * the test. In case the test is not provided, *HtmlUtils::isRelativeURL*
	 * will be used.
	 * 
	 * @param  DOMNode  $node  reference to a DOMNode
	 * @param  string  $attr   name of a node attribute
	 * @param  string  $prefix a string value that the attr value would be prefixed with
	 * @param  function|undefined $test   a test function (callable) that tests
	 *                                    if the provided attr value should be modified by returing
	 *                                    a boolean value
	 * @return DOMNode          the input node reference
	 */
	public static function prefixNodeAttr($node, $attr, $prefix, $test = false) {
		if ($node->hasAttribute($attr)) {
			$val = $node->getAttribute($attr);
			$val = preg_replace_callback('/([^\s,]+)(\s?[^,]*)/', 
				function ($match) use($prefix, $test) {
					$shouldPrefix = is_callable($test) ? $test($match[1]) : self::isRelativeURL($match[1]);
					return ($shouldPrefix ? $prefix : '') . $match[1] . $match[2];
				}
				, $val);
			$node->setAttribute($attr, $val);
		}
		return $node;
	}

	/**
	 * Removes the given prefix from the give node's attribute if the attribute
	 * value starts with the prefix and if the provided test function returns *true*.
	 * 
	 * @param  DOMNode  $node   reference to a DOMNode node
	 * @param  string  $attr   a node attribute name
	 * @param  string  $prefix a prefix that should be stripped from the begining of the attr value
	 * @param  function|undefined $test   a test function (callable) that controls if 
	 *                                    the attribute value should be modified by returning a boolean value
	 * @return DOMNode          the input node reference
	 */
	public static function unprefixNodeAttr($node, $attr, $prefix, $test = false) {
		if ($node->hasAttribute($attr)) {
			$val = $node->getAttribute($attr);
			$val = preg_replace_callback('/([^\s,]+)(\s?[^,]*)/', 
				function ($match) use($prefix, $test) {
					$shouldUnprefix = (strpos($match[1], $prefix) === 0) && 
						(is_callable($test) ? $test($match[1]) : true);
					return ($shouldUnprefix ? substr($match[1], strlen($prefix)) : $match[1]) . $match[2];
				}
				, $val);
			$node->setAttribute($attr, $val);
		}
		return $node;
	}

	/**
	 * Prefixes all given node attributes with the specified value.
	 *
	 * @see HtmlUtils::prefixNodeAttr
	 * 
	 * @param  DOMNode  $node   [description]
	 * @param  string|array  $attrs  attribute name, comma-separated list or array of attribute names
	 * @param  string  $prefix a value to prefix the attribute with
	 * @param  function|undefined $test   change condition function
	 * @return DOMNode          the input node reference
	 */
	public static function prefixNodeAttrs($node, $attrs, $prefix, $test = false) {
		$attrs = is_string($attrs) ? explode(",", $attrs) : $attr;
		foreach ($attrs as $attr) {
			self::prefixNodeAttr($node, trim($attr), $prefix, $test);
		}
		return $node;
	}

	/**
	 * Removes the given prefix from all specified node attributes.
	 *
	 * @see HtmlUtils::unprefixNodeAttr
	 * 
	 * @param  DOMNode  $node   reference to a DOMNode node
	 * @param  string|array  $attrs  attribute name, a comma-separated list or an array of attribute names
	 * @param  string  $prefix a prefix that should be stripped from the begining of the attr value
	 * @param  function|undefined $test   a test function (callable) that controls if 
	 *                                    the attribute value should be modified by returning a boolean value
	 * @return DOMNode          the input node reference
	 */
	public static function unprefixNodeAttrs($node, $attrs, $prefix, $test = false) {
		$attrs = is_string($attrs) ? explode(",", $attrs) : $attr;
		foreach ($attrs as $attr) {
			self::unprefixNodeAttr($node, trim($attr), $prefix, $test);
		}
		return $node;
	}	
}