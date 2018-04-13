<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

class Template extends File {

	protected $_style;
	protected $_type;
	protected $_data;
	protected $_templates = null;

	/**
	 * Constructor
	 *
	 * @param \XF\Entity\Style $style
	 * @param string $type
	 * @param string $name
	 * @param \XF\Entity\Template $data
	 */
	public function __construct($style, $type, $name, $data)
	{
		$this->_name = $name;
		$this->_style = $style;
		$this->_data = $data;
		$this->_type = $type;
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
		Helper::saveTemplate($this->_style->style_id, $this->_data, $data);
	}

	/**
	 * Delete the current file
	 *
	 * @return void
	 * @throws DAV\Exception
	 */
	public function delete()
	{
		if ($this->_data->style_id === 0 || $this->_data->style_id !== $this->_style->style_id) {
			throw new DAV\Exception\Forbidden();
		}

		// Revert template
		$this->_data->delete();
	}

	/**
	 * Get data
	 *
	 * @return string
	 */
	protected function _getContents()
	{
		return $this->_data->template;
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
		$list = explode('.', $this->_name);
		if (count($list) !== 2) {
			return null;
		}

		switch ($list[1]) {
			case 'html':
				return 'text/html';

			case 'css':
			case 'less':
			case 'sass':
			case 'scss':
				return 'text/css';

			case 'txt':
				return 'text/plain';
		}

		return null;
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	public function getLastModified()
	{
		return $this->_data->last_edit_date;
	}
}
