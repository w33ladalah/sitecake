<?php
namespace sitecake;

use Zend\Json\Json as json;

class meta {
	
	static $data = null;
	static $dirty = false;
	
	static function &data() {
		if (!is_array(meta::$data)) {
			meta::$data = meta::load();
		}
		return meta::$data;
	}
	
	static function load() {
		flock::acquire('meta');
		$path = meta::path();
		if (io::file_exists($path)) {
			return json::decode(io::file_get_contents($path), json::TYPE_ARRAY);
		} else {
			return array();
		}
	}
	
	static function save() {
		if (meta::$dirty) {
			meta::$dirty = false;
			io::file_put_contents(meta::path(), json::encode(meta::$data));
		}
		flock::release('meta');
	}
	
	/**
	 * Checks if there is a meta data for the specified object id.
	 * 
	 * @param string $id object id
	 * @return true if the meta data exists, false if not
	 */
	static function exists($id) {
		return array_key_exists($id, meta::data());
	}
	
	/**
	 * Returns the meta data for the specified object id.
	 * 
	 * @param string $id object id
	 * @param string $prop (optional) property name
	 * @return array with all meta data or a single property value if the prop
	 * 			name is specified
	 */
	static function get($id, $prop = null) {
		$data = meta::data();
		return !isset($data[$id]) ? null : (!$prop ? $data[$id] : 
			(isset($data[$id][$prop]) ? $data[$id][$prop] : null));
	}
	
	/**
	 * Returns all meta data that have the given property name and, optionally,
	 * the given value of that property.
	 *
	 * @param mixed $prop
	 * @param mixed $val
	 */
	static function find($prop, $val = null) {
		$result = array();
		foreach (meta::data() as $data) {
			if (isset($data[$prop]) && 
					(!$val || ($val && $data[$prop] == $val))) {
				array_push($result, $data);
			}
		}
		return $result;
	}
	
	/**
	 * Returns all existing meta data IDs.
	 * 
	 * @return array 
	 */
	static function ids() {
		return array_keys(meta::data());
	}
	
	/**
	 * Saves the given meta data for the specified object id. Any existing data
	 * will be replaced.
	 * 
	 * @param string $id object id
	 * @param array $data the object meta data
	 */
	static function put($id, $data) {
		meta::$dirty = true;
		$md = &meta::data();
		$md[$id] = $data;
	}
	
	/**
	 * Updates the object meta data with the new properties. The existing and
	 * the given arrays will be merged (using PHP array_merge function).
	 * 
	 * @param string $id object id
	 * @param array $data the new meta data properties
	 */
	static function update($id, $data) {
		meta::$dirty = true;
		$md = &meta::data();
		$md[$id] = array_merge(meta::get($id), $data);		
	}
	
	/**
	 * Removes any existing object meta data.
	 * 
	 * @param string $id the object id
	 */
	static function remove($id) {
		meta::$dirty = true;
		$md = &meta::data();
		unset($md[$id]);
	}
	
	static function path() {
		return $GLOBALS['DRAFT_CONTENT_DIR'] . '/' . 'meta.data';
	}
}