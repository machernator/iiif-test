<?php

namespace NHM;

class SystemHelper
{
	private static $purifier;
	private static $purifierConfig;
	/**
	 * A fatal error occured. Show error page.
	 *
	 * @param string $msg
	 * @param integer $errorCode
	 * @return void
	 */
	public static function error(string $msg = '', int $errorCode = 0)
	{
		$f3 = \Base::instance();
		$f3->set('SESSION.error', $msg);
		$f3->error(500, $msg);
	}

	/* public static function customErrorHandler(int $errno, string $errstr, string $errfile, int $errline)
	{
		$f3 = \Base::instance();
		$f3->set('SESSION.criticalError', $errstr);
		//$f3->reroute('@error-page');
	} */

	/**
	 * Parse Multidimensional ini File. https://www.php.net/manual/en/function.parse-ini-file.php#114842
	 *
	 * @param string $file
	 * @param boolean $process_sections
	 * @param int $scanner_mode
	 * @return array
	 */
	public static function parse_ini_file_multi($file, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL)
	{
		$explode_str = '.';
		$escape_char = "'";
		// load ini file the normal way
		$data = parse_ini_file($file, $process_sections, $scanner_mode);
		if (!$process_sections) {
			$data = array($data);
		}

		foreach ($data as $section_key => $section) {
			// loop inside the section
			foreach ($section as $key => $value) {
				if (strpos($key, $explode_str)) {
					if (substr($key, 0, 1) !== $escape_char) {
						// key has a dot. Explode on it, then parse each subkeys
						// and set value at the right place thanks to references
						$sub_keys = explode($explode_str, $key);
						$subs = &$data[$section_key];
						foreach ($sub_keys as $sub_key) {
							if (!isset($subs[$sub_key])) {
								$subs[$sub_key] = [];
							}
							$subs = &$subs[$sub_key];
						}
						// set the value at the right place
						$subs = $value;
						// unset the dotted key, we don't need it anymore
						unset($data[$section_key][$key]);
					}
					// we have escaped the key, so we keep dots as they are
					else {
						$new_key = trim($key, $escape_char);
						$data[$section_key][$new_key] = $value;
						unset($data[$section_key][$key]);
					}
				}
			}
		}
		if (!$process_sections) {
			$data = $data[0];
		}

		return $data;
	}

	/**
	 * Rename keys in an array. $keys is an array with the old key as key and the new key as value.
	 * If $deleteUnused is true, all keys not in $keys will be deleted.
	 *
	 * @param array $arr
	 * @param array $keys
	 * @param boolean $deleteUnused
	 * @return void
	 */
	public static function renameKeys(array &$arr, array $keys, $deleteUnused = false)
	{
		if ($deleteUnused) {
			foreach ($arr as $key => $val) {
				if (!array_key_exists($key, $keys)) {
					unset($arr[$key]);
				}
			}
		}

		foreach ($arr as $key => $val) {
			if (array_key_exists($key, $keys)) {
				$arr[$keys[$key]] = $val;
				unset($arr[$key]);
			}
		}
	}

	/**
	 * Status text like errors, warnings or messages can be set by this method.
	 * $msg can be an array of strings or just a string. Only valid in the current
	 * Request. To pass a status to the next request, use saveAppStatus.
	 *
	 * @param string $status
	 * @param mixed $msg
	 * @return void
	 */
	public static function setAppStatus(string $status, $msg)
	{
		$f3 = \Base::instance();
		if (!$f3->get('appstatus')) {
			$f3->set('appstatus', []);
		}
		$f3->push('appstatus', [$status => $msg]);
	}

	/**
	 * Status text like errors, warnings or messages can be set by this method.
	 * $msg can be an array of strings or just a string. Will be stored in Session
	 * and read on the next page load. Use to pass messages to the next request.
	 *
	 * @param string $status
	 * @param mixed $msg
	 * @return void
	 */
	public static function saveAppStatus(string $status, $msg)
	{
		$_SESSION['appstatus'][$status][] = $msg;
	}

	/**
	 * Status text like errors, warnings or messages can be set by this method.
	 * $msg can be an array of strings or just a string. Will be stored in Session
	 * and read on the next page load. If the Session contains appstatus, it will be written
	 * to an F3 Variable. Should be initialised before Render.
	 *
	 * @param string $status
	 * @param mixed $msg
	 * @return void
	 */
	public static function initAppStatus()
	{
		$f3 = \Base::instance();
		$f3->set('appstatus', []);
		// Write
		if (array_key_exists('appstatus', $_SESSION)) {
			foreach($_SESSION['appstatus'] as $key => $value) {
				self::setAppStatus($key, $value);
			}
		}
		self::resetAppStatus();
	}

	/**
	 * Resets all appstatus messages stored in Session. Should be called after reading it in Controller.
	 *
	 * @param string $status
	 * @param mixed $msg
	 * @return void
	 */
	public static function resetAppStatus()
	{
		$_SESSION['appstatus'] = [];
	}


	/**
	 * Create hierarchical Array from Flat Array
	 *
	 * @param   Array  $flat
	 * @param   mixed  $root  parent_id start level
	 *
	 * @return  Array
	 */
	public static function createTree($flat, $root = NULL)
	{
		$parents = array();
		foreach ($flat as $a) {
			$parents[$a['parentID']][] = $a;
		}

		return self::createBranch($parents, $parents[$root]);
	}

	/**
	 * Recursively Add Branch to tree created in self::createTree
	 *
	 * @param   Array  $parents
	 * @param   Array  $children
	 *
	 * @return  Array
	 */
	public static function createBranch(&$parents, $children)
	{
		$tree = array();
		foreach ($children as $child) {
			if (isset($parents[$child['ID']])) {
				$child['children'] =
					self::createBranch($parents, $parents[$child['ID']]);
			}
			$tree[] = $child;
		}
		return $tree;
	}

	/**
	 * Returns Id from passed value. If it is not a valid int, return 0.
	 *
	 * @param   mixed  $id
	 *
	 * @return  int
	 */
	public static function validId($id): int
	{
		if (filter_var($id, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) === false) {
			return 0;
		}
		return $id;
	}

	/**
	 * Recursively trim all strings in an array
	 *
	 * @param   array  &$arr
	 *
	 * @return void
	 */
	public static function trimArray(array &$arr)
	{
		foreach ($arr as $key => $value) {
			if (is_string($value)) {
				$arr[$key] = trim($value);
			} elseif (is_array($value)) {
				self::trimArray($value);
				$arr[$key] = $value;
			}
		}
	}

	/**
	 * Remove all HTML Tags from a string
	 *
	 * @param   string  $text
	 *
	 * @return  string 	cleaned up Text
	 */
	public static function removeHTML(string $text): string
	{
		// Create purifier objects only once
		if (!self::$purifierConfig || !self::$purifier) {
			self::$purifierConfig = \HTMLPurifier_HTML5Config::createDefault();
			self::$purifierConfig->set('HTML.AllowedElements', []);
			self::$purifierConfig->set('AutoFormat.RemoveEmpty', true);
			self::$purifier = new \HTMLPurifier(self::$purifierConfig);
		}

		// HACK: preserve single ampersands
		$text = str_replace('&amp;', '&', $text);
		$text = str_replace('&', '#######!', $text);
		$purified = self::$purifier->purify($text);
		$purified = str_replace('#######!', '&', $text);
		return $purified;
	}

	/**
	 * Returns random entries from Array
	 *
	 * @param   Array  $arr
	 * @param   int  $max
	 * @param   int  $min
	 *
	 * @return  Array
	 */
	public static function randomFromArray(array $arr, int $max = 5, int $min = 1): array
	{
		$nr = rand($min, $max);
		$res = [];
		$rand = array_rand($arr, $nr);
		foreach ($rand as $key) {
			$res[] = (array)$arr[$key];
		}

		return $res;
	}

	/**
	 * Recursively Creates String from keys and values of array
	 *
	 * @param   array   $arr
	 *
	 * @return  string
	 */
	public static function arrayToString(array &$arr): string
	{
		$str = '';

		foreach ($arr as $key => $val) {
			if (is_array($val)) {
				$val = self::arrayToString($val);
			}
			$str .= "$key: $val \n";
		}

		return $str;
	}

	/**
	 * Recursively read directory and return folders & files as array tree
	 *
	 * @param   string  $dir
	 *
	 * @return  array
	 */
	public static function directoryTree(string $dir): array
	{
		$result = [];

		if (!is_dir($dir)) {
			return $result;
		}

		$cdir = scandir($dir);
		foreach ($cdir as $key => $value) {
			if (!in_array($value, array(".", ".."))) {
				if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
					$result[$value] = self::directoryTree($dir . DIRECTORY_SEPARATOR . $value);
				} else {
					$result[] = $value;
				}
			}
		}

		return $result;
	}

	/**
	 * Read only files from Folder
	 *
	 * @param   string  $dir
	 *
	 * @return  array
	 */
	public static function filesInFolder(string $dir, string $category = ''): array
	{
		$files = [];
		$f3 = \Base::instance();
		$basePath = $f3->get('filePath');

		if (!is_dir($dir)) {
			return $files;
		}

		$contents = scandir($dir);
		foreach ($contents as $key => $name) {
			if (is_file($dir . $name)) {
				// prevent hidden Files like .DS_Store
				if (mb_substr($name, 0, 1) === '.') {
					continue;
				}

				$pos = strrpos($name, '.');
				$suffix =  substr($name, $pos + 1) ?? '';

				// mime type
				$fi = new \finfo(FILEINFO_MIME);
				$mime_type = $fi->file($dir . $name);
				if (!$mime_type) {
					$mime_type = '';
				} else {
					// only use type info - char encoding etc. not necessary
					$mime_type = explode(';', $mime_type)[0];
				}

				$files[] = [
					'name' => $name,
					'path' => str_replace($basePath, '', $dir),
					'size' => filesize($dir . $name),
					'mime_type' => $mime_type ?? '',
					'suffix' => $suffix,
					'category' => $category
				];
			}
		}
		return $files;
	}

	/**
	 * Generate random string as long as the provided length.
	 *
	 * @param   int     $length
	 *
	 * @return  string
	 */
	public static function randomString(int $length):string {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
		}

		return $randomString;
	}

	public static function array_diff_assoc_recursive($array1, $array2)
	{
		$difference = [];
		foreach($array1 as $key => $value)
		{
			if(is_array($value))
			{
				  if(!isset($array2[$key]))
				  {
					  $difference[$key] = $value;
				  }
				  elseif(!is_array($array2[$key]))
				  {
					  $difference[$key] = $value;
				  }
				  else
				  {
					  $new_diff = self::array_diff_assoc_recursive($value, $array2[$key]);
					  if($new_diff != FALSE)
					  {
							$difference[$key] = $new_diff;
					  }
				  }
			  }
			  elseif(!array_key_exists($key, $array2) || $array2[$key] != $value)
			  {
				  $difference[$key] = $value;
			  }
		}
		return $difference;
	}

	 /**
     * Convert empty string values to null. Better for handling in Database
     *
     * @param   array  $params
     *
     * @return  void
     */
    public static function emptyStringToNull(array &$params) {
        foreach ($params as $key => $value) {
            if ($value === '') {
                $params[$key] = null;
            }
			elseif (is_array($value)) {
				self::emptyStringToNull($value);
				$params[$key] = $value;
			}
        }
    }

	/**
	 * Usually datasets have meta information like date_created, user_created, date_updated, user_updated.
	 * If they are not set, we want to display a placeholder instead of an empty string.
	 *
	 * @param array $data
	 * @param string $noValue
	 * @return array
	 */
	public static function getMetaInfo(array $data, string $noValue='&mdash;'):array
	{
		$meta = [];
		$meta['date_created'] = $data['date_created'] ? $data['date_created']->format('Y-m-d H:i') : $noValue;
		$meta['user_created'] = $data['user_created'] ? $data['user_created'] : $noValue;
		$meta['date_updated'] = $data['date_updated'] ? $data['date_updated']->format('Y-m-d H:i') : $noValue;
		$meta['user_updated'] = $data['user_updated'] ? $data['user_updated'] : $noValue;
		return $meta;
	}
}
