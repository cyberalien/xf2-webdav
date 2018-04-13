<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;
use CyberAlien\XFStyleProperties\Property;

class AllStyleProperties extends File {

	protected $_style;

	/**
	 * Constructor
	 *
	 * @param \XF\Entity\Style $style
	 * @param string $name
	 */
	public function __construct($style, $name)
	{
		$this->_name = $name;
		$this->_style = $style;
	}

	/**
	 * Updates the data
	 *
	 * @param resource $data
	 * @return void
	 * @throws DAV\Exception
	 */
	public function put($data)
	{
		$data = Helper::resourceToString($data);
		$tokens = Property::tokenizeLess($data);

		/* @var \XF\Repository\StyleProperty $repo */
		$repo = \XF::repository('XF:StyleProperty');
		$properties = $repo->getEffectivePropertiesInStyle($this->_style);

		foreach ($properties as $prop) {
			if ($prop->value_type === 'template') {
				continue;
			}

			$converter = new Property($prop->property_name, $prop);
			if ($converter->fromLess($data, $tokens) && $converter->updated) {
				Helper::saveProperty($this->_style->style_id, $prop, $prop->property_value);
			}
		}
	}

	/**
	 * Delete the current file
	 *
	 * @return void
	 * @throws DAV\Exception
	 */
	public function delete()
	{
		throw new DAV\Exception\Forbidden();
	}

	/**
	 * Get data
	 *
	 * @return string
	 */
	protected function _getContents()
	{
		/* @var \XF\Repository\StyleProperty $repo */
		$repo = \XF::repository('XF:StyleProperty');
		$properties = $repo->getEffectivePropertiesInStyle($this->_style);
		$groups = $repo->getEffectivePropertyGroupsInStyle($this->_style);

		// Find all groups and sort properties
		$sorted = [];
		foreach ($properties as $prop) {
			if ($prop->value_type === 'template') {
				continue;
			}
			$group = $prop->group_name;
			if (!isset($sorted[$group])) {
				$sorted[$group] = [];
			}
			$sorted[$group][] = $prop;
		}

		foreach ($sorted as $group => &$items) {
			uasort($items, function($a, $b) {
				$aOrder = $a->display_order;
				$bOrder = $b->display_order;

				if ($aOrder == $bOrder) {
					return 0;
				}

				return ($aOrder < $bOrder ? -1 : 1);
			});
		}

		$sortedItems = [];
		foreach ($groups as $groupTitle => $group) {
			if (!isset($sorted[$groupTitle])) {
				continue;
			}
			$items = $sorted[$groupTitle];
			unset($sorted[$groupTitle]);
			$sortedItems[] = [$groupTitle, $items];
		}
		foreach ($sorted as $groupTitle => $items) {
			$sortedItems[] = ['', $items];
		}

		// Print all properties
		$output = '';
		foreach ($sortedItems as list($groupTitle, $items)) {
			$output .= "/**\n * " . $groupTitle . "\n */\n";
			foreach ($items as $prop) {
				$converter = new Property($prop->property_name, $prop);
				$output .= $converter->exportLessCode(true);
			}
			$output .= "\n";
		}

		return $output;
	}

	/**
	 * Returns the mime-type for a file
	 *
	 * If null is returned, we'll assume application/octet-stream
	 *
	 * @return mixed
	 */
	public function getContentType()
	{
		return 'text/css';
	}
}
