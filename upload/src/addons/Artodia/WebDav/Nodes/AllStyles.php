<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class AllStyles extends Directory {

	/**
	 * Constructor
	 *
	 * @param $name
	 */
	public function __construct($name)
	{
		$this->_name = $name;
	}

	/**
	 * Get all items
	 */
	protected function _getItems()
	{
		/* @var \XF\Repository\Style $stylesRepo */
		$stylesRepo = \XF::repository('XF:Style');
		$styles = $stylesRepo->findStyles()->fetch();

		$this->_items = [];
		foreach ($styles as $style) {
			$this->_items[$this->_getStyleDirectory($style)] = $style;
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
		return new Style($name, $data);
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	function getLastModified()
	{
		if ($this->_items === null) {
			$this->_getItems();
		}

		$max = 0;
		foreach ($this->_items as $title => $data) {
			$max = max($max, $data->last_modified_date);
		}

		return $max;
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
