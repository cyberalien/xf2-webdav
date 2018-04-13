<?php

namespace Artodia\WebDav\Nodes;

use Sabre\DAV;

abstract class Directory extends Node implements DAV\ICollection, DAV\IQuota {
	protected $_items = null;

	/**
	 * Returns a specific child node, referenced by its name
	 *
	 * This method must throw DAV\Exception\NotFound if the node does not
	 * exist.
	 *
	 * @param string $name
	 * @return DAV\INode
	 * @throws DAV\Exception
	 */
	public function getChild($name)
	{
		if ($this->_items === null) {
			$this->_getItems();
		}

		if (!isset($this->_items[$name])) {
			throw new DAV\Exception\NotFound('Directory ' . $name . ' cannot be found.');
		}

		return $this->_getNode($name, $this->_items[$name]);
	}

	/**
	 * Returns an array with all the child nodes
	 *
	 * @return DAV\INode[]
	 */
	public function getChildren()
	{
		if ($this->_items === null) {
			$this->_getItems();
		}

		$nodes = [];
		foreach ($this->_items as $name => $data) {
			$nodes[] = $this->getChild($name);
		}

		return $nodes;
	}

	/**
	 * Checks if a child exists.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function childExists($name)
	{
		if ($this->_items === null) {
			$this->_getItems();
		}

		return isset($this->_items[$name]);
	}

	/**
	 * Create file
	 *
	 * @param string $name Name of the file
	 * @param resource|string $data Initial payload
	 *
	 * @return null|string
	 * @throws DAV\Exception
	 */
	public function createFile($name, $data = null)
	{
		$child = $this->getChild($name);
		if ($child instanceof DAV\IFile) {
			$child->put($data);
		} else {
			throw new DAV\Exception\Forbidden();
		}
		return null;
	}

	/**
	 * Creates a new subdirectory
	 *
	 * @param string $name
	 *
	 * @return void
	 * @throws DAV\Exception
	 */
	public function createDirectory($name)
	{
		throw new DAV\Exception\Forbidden();
	}

	/**
	 * Deletes all files in this directory, and then itself
	 *
	 * @return void
	 * @throws DAV\Exception
	 */
	public function delete()
	{
		throw new DAV\Exception\NotImplemented();
	}

	/**
	 * Returns available diskspace information
	 *
	 * @return array
	 */
	public function getQuotaInfo()
	{
		// Return dummy numbers: 32Mb available, 1Mb used
		return [1048576 * 32, 1048576];
	}

	/**
	 * Get list of items
	 */
	abstract protected function _getItems();

	/**
	 * Get node for item
	 *
	 * @param string $name
	 * @param mixed $data
	 *
	 * @return DAV\INode
	 */
	abstract protected function _getNode($name, $data);
}
