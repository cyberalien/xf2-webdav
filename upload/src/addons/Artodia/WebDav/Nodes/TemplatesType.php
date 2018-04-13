<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class TemplatesType extends Directory {

	protected $_style;
	protected $_type;
	protected $_filter;

	/**
	 * Constructor
	 *
	 * @param $style
	 * @param $name
	 * @param $filter
	 */
	function __construct($style, $name, $type, $filter)
	{
		$this->_name = $name;
		$this->_type = $type;
		$this->_style = $style;
		$this->_filter = $filter;
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
		return new Template($this->_style, $this->_type, $name, $data);
	}

	/**
	 * Get all items
	 */
	protected function _getItems()
	{
		/* @var \XF\Repository\Template $templateRepo */
		$templateRepo = \XF::repository('XF:Template');

		$filter = $this->_filter;
		$templates = $templateRepo->$filter($this->_style, $this->_type)->fetch();

		$this->_items = [];
		foreach ($templates as $template) {
			// $template properties: template_id, type, title, style_id, template, template_parsed, addon_id, version_id, version_string, disable_modifications, last_edit_date
			$this->_items[$this->_normalizeTitle($template->title)] = $template;
		}
	}

	/**
	 * Add extension to template name
	 *
	 * @param $title
	 *
	 * @return string
	 */
	protected function _normalizeTitle($title)
	{
		$list = explode('.', $title);
		if (count($list) === 1) {
			return $title . '.html';
		}
		return $title;
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
