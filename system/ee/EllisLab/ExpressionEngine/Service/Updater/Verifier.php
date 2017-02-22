<?php

namespace EllisLab\ExpressionEngine\Service\Updater;

use EllisLab\ExpressionEngine\Service\Updater\UpdaterException;
use EllisLab\ExpressionEngine\Library\Filesystem\Filesystem;

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2016, EllisLab, Inc.
 * @license		https://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 4.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine Updater file verification class
 *
 * @package		ExpressionEngine
 * @subpackage	Updater
 * @category	Service
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class Verifier {

	protected $filesystem = NULL;

	/**
	 * Constructor
	 *
	 * @param	Filesystem	$filesystem	Filesystem library object
	 */
	public function __construct(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
	}

	/**
	 * Given a path to a directory and a manifest of its files' respective hashes,
	 * makes sure those hashes match the files on the filesystem
	 *
	 * @param	string	$path		Path to directory to check
	 * @param	string	$hash_path	Path to location of hash manifest file
	 * @param	string	$subpath	Optional subpath inside hashmap to limit verification to that path
	 * @param	array	$exclusions	Array of any paths to exlude when verifying
	 */
	public function verifyPath($path, $hash_path, $subpath = '', Array $exclusions = [])
	{
		$hashmap = $this->createHashmap($this->filesystem->read($hash_path));
		$subpath = ltrim($subpath, '/');

		$missing_files = [];
		$corrupt_files = [];

		foreach ($hashmap as $file_path => $hash)
		{
			// If a subpath was specified but the current file is not in that path, skip it
			if ( ! empty($subpath) && substr($file_path, 0, strlen($subpath)) !== $subpath)
			{
				continue;
			}

			// Skip paths we don't want to verify
			foreach ($exclusions as $exclude)
			{
				if (substr($file_path, 0, strlen($exclude)) === $exclude)
				{
					continue 2;
				}
			}

			// Abosulute server path to the file in question
			if (empty($subpath))
			{
				$absolute_file_path = $path . DIRECTORY_SEPARATOR . $file_path;
			}
			else
			{
				$absolute_file_path = $path . str_replace($subpath, '', $file_path);
			}

			// Does the file even exist?
			if ( ! $this->filesystem->exists($absolute_file_path))
			{
				$missing_files[] = $file_path;
			}
			// If so, does it have integrity?
			else if ($this->filesystem->sha1File($absolute_file_path) !== $hash)
			{
				$corrupt_files[] = $file_path;
			}
		}

		if ( ! empty($missing_files))
		{
			throw new UpdaterException(
				sprintf(
					lang('could_not_find_files'),
					implode("\n", $missing_files)
				),
			9);
		}

		if ( ! empty($corrupt_files))
		{
			throw new UpdaterException(
				sprintf(
					lang('could_not_verify_file_integrity'),
					implode("\n", $corrupt_files)
				),
			10);
		}

		return TRUE;
	}

	/**
	 * Given a string of rows of hashes and filenames seprated by a single space,
	 * creates an array indexed by file path of the corresponding hashes
	 *
	 * @param	string	$hashmap	Hash manifest file contents
	 */
	protected function createHashmap($hashmap)
	{
		$lines = explode("\n", $hashmap);

		$hashmap = [];
		foreach ($lines as $line) {
			$line_parts = explode(' ', $line);
			if (count($line_parts) == 2)
			{
				$hashmap[$line_parts[1]] = $line_parts[0];
			}
		}

		return $hashmap;
	}
}

// EOF
