<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class StylesTree extends Directory {

	protected $_style;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param mixed $style
	 */
	public function __construct($name, $style = null)
	{
		$this->_name = $name;

		if ($style === null) {
			$this->_style = \XF::repository('XF:Style')->getMasterStyle();
		} else {
			$this->_style = $style;
		}
	}

	/**
	 * Get all items
	 */
	protected function _getItems()
	{
		$styleId = $this->_style->style_id;

		// Get template types
		$this->_items = Helper::getStyleComponents($this->_style);

		// Get all child styles
		/* @var \XF\Repository\Style $stylesRepo */
		$stylesRepo = \XF::repository('XF:Style');
		$styles = $stylesRepo->findStyles()->fetch();
		foreach ($styles as $style) {
			if ($style->parent_id === $styleId) {
				$this->_items[$this->_getStyleDirectory($style)] = $style;
			}
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
		$node = Helper::getStyleComponentNode($this->_style, $name, $data);
		if ($node !== null) {
			return $node;
		}

		return new StylesTree($name, $data);
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
	 * Get style directory string
	 *
	 * @param $style
	 * @return string
	 */
	protected function _getStyleDirectory($style)
	{
		$title = trim($this->_romanize($style->title));
		if (!strlen($title)) {
			$title = 'Style';
		}

		// Hidden?
//		if (!$style->user_selectable) {
//			$title .= ' (hidden)';
//		}

		// Parent and style ids
//		if ($style->parent_id) {
//			$title .= ' (child of ' . $style->parent_id . ')';
//		}

		// Add style id
		$title .= ' (' . $style->style_id . ')';
		return $title;
	}
}
