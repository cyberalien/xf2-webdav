<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class StyleProperties extends Directory {

	protected $_style;
	protected $_filters;

	/**
	 * Constructor
	 *
	 * @param $style
	 * @param $name
	 * @param $filters
	 */
	function __construct($style, $name, $filters = null)
	{
		$this->_name = $name;
		$this->_style = $style;
		$this->_filters = [
			'customized'    => $filters !== null && !empty($filters['customized']),
			'group' => $filters !== null && !empty($filters['group']) ? $filters['group'] : ''
		];
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
		return new StyleProperty($this->_style, $name, $data[0], $data[1]);
	}

	/**
	 * Get all items
	 */
	protected function _getItems()
	{
		$styleId = $this->_style->style_id;

		/* @var \XF\Repository\StyleProperty $repo */
		$repo = \XF::repository('XF:StyleProperty');
		$properties = $repo->getEffectivePropertiesInStyle($this->_style);

		$this->_items = [];
		foreach ($properties as $prop) {
			// $prop fields: property_id, property_name, style_id, group_name, title, description, property_type, css_components, value_type, value_parameters, depends_on, value_group, property_value, addon_id, display_order

			if ($this->_filters['customized'] && $prop->style_id !== $styleId) {
				continue;
			}
			if ($this->_filters['group'] && $prop->group_name !== $this->_filters['group']) {
				continue;
			}

			$title = $prop->property_name;
			$type = 'txt';

			if ($prop->value_type === 'template') {
				$type = 'json';
				$title .= '.json';
			} elseif ($prop->property_type === 'css') {
				$title .= '.less';
				$type = 'less';
			} else {
				$title .= '.txt';
			}

			$this->_items[$title] = [$type, $prop];
		}
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
}
