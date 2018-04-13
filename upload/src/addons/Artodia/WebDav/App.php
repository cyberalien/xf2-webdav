<?php

namespace Artodia\WebDav;

use Sabre\DAV;

class App
{
	protected $_user;

	/**
	 * Run application
	 */
	public function run()
	{
		$dir = new Nodes\Root();

		// Create server
		$server = new DAV\Server($dir);
		$server->setBaseUri($this->_getBaseUri());

		// Authentication
		$auth = new DAV\Auth\Backend\BasicCallBack([$this, 'authCallback']);
		$auth->setRealm('XenForo WebDav');
		$authPlugin = new DAV\Auth\Plugin($auth);
		$server->addPlugin($authPlugin);

		// Browser plugin for debug
		$server->addPlugin(new DAV\Browser\Plugin());

		// Run server
		$server->exec();
	}

	/**
	 * Authentication callback function
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return bool
	 */
	public function authCallback($username, $password)
	{
		// Find user
		$user = \XF::em()->findOne('XF:User', ['username' => $username], ['Auth']);
		if (!$user) {
			return false;
		}

		// Check password
		$auth = $user->Auth;
		if (!$auth || !$auth->authenticate($password)) {
			return false;
		}

		// Check if user is admin
		if (!$user->is_admin) {
			return false;
		}

		$this->_user = $user;
		return true;
	}

	/**
	 * Get base uri
	 *
	 * @return string
	 */
	protected function _getBaseUri()
	{
		if (isset($_SERVER['SCRIPT_NAME'])) {
			return $_SERVER['SCRIPT_NAME'];
		}

		if (isset($_SERVER['REQUEST_URI'])) {
			$script = 'admindav.php';
			$parts = explode($script, $_SERVER['REQUEST_URI']);
			if (count($parts) > 1) {
				return $parts[0] . $script;
			}
		}

		throw new \RuntimeException('Cannot get base uri. Check web server configuration.');
	}
}
