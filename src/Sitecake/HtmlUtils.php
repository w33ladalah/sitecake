<?php

namespace Sitecake;

class HtmlUtils {

	/**
	 * Append the give HTML code to the HTML page head section.
	 * 
	 * @param  string|phpQueryObject $html  html page or a phpQueryObject
	 * @param  [type] $code [description]
	 * @return [type]       [description]
	 */
	public static function appendToHead($html, $code) {
		return phpQuery::pq('head', $html)->append($code);
	}
}