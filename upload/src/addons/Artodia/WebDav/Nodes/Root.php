<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class Root extends Directory {
	public static $paths = [
		'all styles'  => 'Artodia\\WebDav\\Nodes\\AllStyles',
		'master style'  => 'Artodia\\WebDav\\Nodes\\StylesTree',
		'styles'    => ['Sabre\\DAV\\FS\\Directory', 'styles']
	];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_name = '';
	}

	/**
	 * Returns a specific child node, referenced by its name
	 *
	 * @param string $name
	 * @param mixed $data
	 * @return DAV\INode
	 */
	protected function _getNode($name, $data)
	{
		$className = $data;
		$param = $name;
		if (is_array($className)) {
			$param = $className[1];
			$className = $className[0];
		}

		return new $className($param);
	}

	/**
	 * Get all items
	 */
	protected function _getItems()
	{
		$this->_items = static::$paths;
	}
}
