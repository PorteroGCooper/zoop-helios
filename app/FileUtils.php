<?php

/**
 * FileUtils class.
 * 
 * @group FileUtils
 * 
 * A collection of file utilities.
 * 
 * @endgroup
 */
abstract class FileUtils {

	/**
	 * Check if a file or directory is writable.
	 * 
	 * This is an improvement over the stock PHP is_writable(). Returns true if the file
	 * exists and is writable, or if the file doesn't exist but is creatable.
	 * 
	 * @access public
	 * @param mixed $path
	 * @return void
	 */
	static function isWritable($path) {
		$ret_val = false;
		$path_chunks = explode('/', $path);
		
		if (file_exists($path) && is_writable($path)) {
			$ret_val = true;
		} else {
			$end = array_pop($path_chunks);
			
			if (!empty($path_chunks)) {
				$new_path = implode('/', $path_chunks);
				$ret_val = self::isWritable($new_path);
			}
		}
		return $ret_val;
	}
}