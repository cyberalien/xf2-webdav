<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

abstract class Node implements DAV\INode {

	protected $_name;

	/**
	 * Returns the name of the node
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Renames the node
	 *
	 * @param string $name The new name
	 * @return void
	 * @throws DAV\Exception
	 */
	public function setName($name)
	{
		throw new DAV\Exception\NotImplemented();
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	public function getLastModified()
	{
		return 0;
	}

	/**
	 * Romanize URL. Copied from \XF\Mvc\Router
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 */
	protected function _romanize($string)
	{
		$string = strval($string);

		$string = utf8_romanize(utf8_deaccent($string));

		$string = strtr(
			$string,
			'`!"$%^&*()-+={}[]<>;:@#~,./?|' . "\r\n\t\\",
			'                             ' . '    '
		);
		$string = strtr($string, ['"' => '', "'" => '']);

		$string = preg_replace('/[^a-zA-Z0-9_ -]/', '', $string);

		$string = preg_replace('/[ ]+/', '-', trim($string));
		$string = strtr($string, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
		$string = urlencode($string);

		return $string;
	}
}
