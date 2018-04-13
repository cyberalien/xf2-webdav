<?php

namespace Artodia\WebDav\Nodes;

class Stream {
	protected static $_registered = false;
	protected static $_protocol = 'xft';
	protected static $_lastId = 0;

	protected static $_cache = [];
	protected static $_time = [];

	protected $_position;
	protected $_id;
	protected $_contents;

	/**
	 * Set template contents and return stream URL
	 *
	 * @param string $data
	 * @param int $mtime
	 *
	 * @return string
	 */
	public static function createStream($data, $mtime)
	{
		$id = static::$_lastId ++;
		static::$_cache[$id] = $data;
		static::$_time[$id] = $mtime;
		return static::protocol() . '://' . $id;
	}

	/**
	 * Open stream
	 *
	 * @param $path
	 * @param $mode
	 * @param $options
	 * @param $opened_path
	 *
	 * @return bool
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$url = parse_url($path);
		$id = intval($url['host']);

		$this->_id = $id;
		$this->_position = 0;
		$this->_contents = static::$_cache[$id];

		return true;
	}

	/**
	 * Read stream
	 *
	 * @param $count
	 * @return string
	 */
	public function stream_read($count)
	{
		$result = substr($this->_contents, $this->_position, $count);
		$this->_position += strlen($result);
		return $result;
	}

	/**
	 * Return current position
	 *
	 * @return int
	 */
	public function stream_tell()
	{
		return $this->_position;
	}

	/**
	 * Check if end has been reached
	 *
	 * @return bool
	 */
	public function stream_eof()
	{
		return $this->_position >= strlen($this->_contents);
	}

	/**
	 * Change current position
	 *
	 * @param $offset
	 * @param $whence
	 *
	 * @return bool
	 */
	public function stream_seek($offset, $whence)
	{
		$length = strlen($this->_contents);

		switch ($whence) {
			case SEEK_SET:
				break;

			case SEEK_CUR:
				$offset = $this->_position + $offset;
				break;

			case SEEK_END:
				$offset = $length + $offset;
				break;

			default:
				return false;
		}

		if ($offset < $length && $offset >= 0) {
			$this->_position = $offset;
			return true;
		}
		return false;
	}

	/**
	 * Stat
	 */
	public function stream_stat()
	{
		$time = static::$_time[$this->_id];
		return [
			'size'  => strlen($this->_contents),
			'atime' => $time,
			'mtime' => $time,
			'ctime' => $time
		];
	}

	/**
	 * Register wrapper and get protocol used by wrapper
	 *
	 * @return string
	 */
	public static function protocol()
	{
		if (!static::$_registered) {
			stream_wrapper_register(static::$_protocol, get_called_class());
			static::$_registered = true;
		}
		return static::$_protocol;
	}
}
