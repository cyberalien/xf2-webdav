<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class StylePropertyGroups extends Directory {

	protected $_style;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param mixed $style
	 */
	public function __construct($name, $style)
	{
		$this->_name = $name;
		$this->_style = $style;
	}

	/**
	 * Get all items
	 */
	protected function _getItems()
	{
		$this->_items = [];

		/* @var \XF\Repository\StyleProperty $repo */
		$repo = \XF::repository('XF:StyleProperty');
		$properties = $repo->getEffectivePropertiesInStyle($this->_style);

		foreach ($properties as $prop) {
			$this->_items[$prop->group_name] = true;
		}
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
		return new StyleProperties($this->_style, $name, [
			'group' => $name
		]);
	}
}
