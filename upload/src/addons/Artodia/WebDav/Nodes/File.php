<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

abstract class File extends Node implements DAV\IFile {

	protected $_filename = null;

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		if ($this->_filename !== null) {
			@unlink($this->_filename);
		}
	}

	/**
	 * Returns the data
	 *
	 * @return resource
	 */
	public function get()
	{
		if ($this->_filename === null) {
			$contents = $this->_getContents();
			$time = $this->getLastModified();

			$this->_filename = tempnam(sys_get_temp_dir(), 'xf');
			file_put_contents($this->_filename, $contents);
			if ($time) {
				@touch($this->_filename, $time);
			}
		}

		return fopen($this->_filename, 'r');
	}

	/**
	 * Returns the size of the node, in bytes
	 *
	 * @return int
	 */
	public function getSize()
	{
		return strlen($this->_getContents());
	}

	/**
	 * Returns the ETag for a file
	 *
	 * An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
	 * The ETag is an arbitrary string, but MUST be surrounded by double-quotes.
	 *
	 * Return null if the ETag can not effectively be determined
	 *
	 * @return mixed
	 */
	public function getETag()
	{
		return '"' . sha1(
				$this->_getContents() .
				$this->getLastModified()
			) . '"';
	}

	/**
	 * Get data
	 *
	 * @return string
	 */
	abstract protected function _getContents();

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	public function getLastModified()
	{
		return 0;
	}
}
