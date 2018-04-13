<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;
use CyberAlien\XFStyleProperties\Property;

class StyleProperty extends File {

	protected $_style;
	protected $_data;
	protected $_format;

	/**
	 * Constructor
	 *
	 * @param \XF\Entity\Style $style
	 * @param string $name
	 * @param string $format
	 * @param \XF\Entity\Template $data
	 */
	public function __construct($style, $name, $format, $data)
	{
		$this->_name = $name;
		$this->_style = $style;
		$this->_format = $format;
		$this->_data = $data;
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
		switch ($this->_format) {
			case 'less':
				$prop = new Property($this->_data->property_name, $this->_data);
				$prop->fromLess($data);
				$data = $prop->getValue();
				break;

			case 'json':
				$data = json_decode($data, true);
				break;
		}
		Helper::saveProperty($this->_style->style_id, $this->_data, $data);
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
		switch ($this->_format) {
			case 'less':
				$prop = new Property($this->_data->property_name, $this->_data);
				return $prop->exportLessCode(true);

			case 'json':
				return json_encode($this->_data->property_value);
		}

		$value = $this->_data->property_value;
		if (is_array($value)) {
			$value = json_encode($value);
		}
		return $value;
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
		switch ($this->_format) {
			case 'less':
				return 'text/css';

			case 'json':
				return 'application/json';

			default:
				return 'text/plain';
		}
	}
}
