<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class Style extends Directory {

	protected $_style;

	/**
	 * Constructor
	 *
	 * @param $name
	 * @param $data
	 */
	function __construct($name, $data)
	{
		$this->_name = $name;
		$this->_style = $data;
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
		return Helper::getStyleComponentNode($this->_style, $name, $data);
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	function getLastModified()
	{
		return $this->_style->last_modified_date;
	}

	/**
	 * Get all items
	 */
	protected function _getItems()
	{
		$this->_items = Helper::getStyleComponents($this->_style);
	}
}
