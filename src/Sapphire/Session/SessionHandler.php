<?php
/**
 * Sapphire
 *
 * Licensed under the Massachusetts Institute of Technology
 *
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Lorne Wang < post@lorne.wang >
 * @copyright   Copyright (c) 2014 - 2015 , All rights reserved.
 * @link        http://lorne.wang/projects/sapphire
 * @license     http://lorne.wang/licenses/MIT
 */
namespace Sapphire\Session;

use SessionHandlerInterface;

/**
 * Session Handler Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
abstract class SessionHandler implements SessionHandlerInterface
{
	protected $options;

	/**
	 * Data fingerprint
	 *
	 * @var	boolean
	 */
	protected $fingerprint;

	/**
	 * Lock placeholder
	 *
	 * @var	mixed
	 */
	protected $lock = FALSE;

	/**
	 * Read session ID
	 *
	 * Used to detect session_regenerate_id() calls because PHP only calls
	 * write() after regenerating the ID.
	 *
	 * @var	string
	 */
	protected $sessionId;

	// ------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  array &$options Configuration parameters
	 */
	public function __construct(&$options)
	{
		$this->options =& $options;
	}

	// ------------------------------------------------------------------------

	/**
	 * Cookie destroy
	 *
	 * Internal method to force removal of a cookie by the client
	 * when session_destroy() is called.
	 *
	 * @access public
	 * @return boolean
	 */
	protected function cookieDestroy()
	{
		return setcookie(
			$this->options['cookie_name'],
			NULL,
			1,
			$this->options['cookie_path'],
			$this->options['cookie_domain'],
			$this->options['cookie_secure'],
			TRUE
		);
	}

	// ------------------------------------------------------------------------

	/**
	 * Get lock
	 *
	 * A dummy method allowing drivers with no locking functionality
	 * (databases other than PostgreSQL and MySQL) to act as if they
	 * do acquire a lock.
	 *
	 * @access public
	 * @param  string $sessionId
	 * @return boolean
	 */
	protected function getLock($sessionId)
	{
		$this->lock = TRUE;
		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Release lock
	 *
	 * @access public
	 * @return boolean
	 */
	protected function releaseLock()
	{
		if ($this->lock)
		{
			$this->lock = FALSE;
		}

		return TRUE;
	}
}
