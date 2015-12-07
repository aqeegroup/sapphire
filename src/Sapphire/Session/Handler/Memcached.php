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
namespace Sapphire\Session\Handler;

use Sapphire\Session\SessionHandler;

/**
 * Session Handler for Memcached
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Memcached extends SessionHandler
{
	/**
	 * Memcached instance
	 *
	 * @var \Memcached
	 */
	protected $memcached;

	/**
	 * Key prefix
	 *
	 * @var string
	 */
	protected $keyPrefix = 'php_session:';

	/**
	 * Lock key
	 *
	 * @var string
	 */
	protected $lockKey;

	/**
	 * Constructor
	 *
	 * @param  array &$options Configuration parameters
	 */
	public function __construct(&$options)
	{
		parent::__construct($options);

		if (empty($this->options['save_path']))
		{
			trigger_error('Session: No Memcached save path configured.', E_USER_ERROR);
		}

		if ($this->options['match_ip'] === TRUE)
		{
			$this->keyPrefix .= $_SERVER['REMOTE_ADDR'] . ':';
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Sanitizes save_path and initializes connections.
	 *
	 * @param  string $savePath    Server path(s)
	 * @param  string $sessionName Session cookie name, unused
	 * @return boolean
	 */
	public function open($savePath, $sessionName)
	{
		$this->memcached = new \Memcached();
		$this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, TRUE); // required for touch() usage
		$serverList = [];
		foreach ($this->memcached->getServerList() as $server)
		{
			$serverList[] = $server['host'] . ':' . $server['port'];
		}

		if ( ! preg_match_all('#,?([^,:]+)\:(\d{1,5})(?:\:(\d+))?#', $this->options['save_path'], $matches, PREG_SET_ORDER))
		{
			$this->memcached = NULL;
			trigger_error('Session: Invalid Memcached save path format: ' . $this->options['save_path'], E_USER_ERROR);

			return FALSE;
		}

		foreach ($matches as $match)
		{
			// If Memcached already has this server (or if the port is invalid), skip it
			if (in_array($match[1] . ':' . $match[2], $serverList, TRUE))
			{
				continue;
			}

			if ( ! $this->memcached->addServer($match[1], $match[2], isset($match[3]) ? $match[3] : 0))
			{
				trigger_error('Could not add ' . $match[1] . ':' . $match[2] . ' to Memcached server pool.', E_USER_ERROR);
			}
			else
			{
				$serverList[] = $match[1] . ':' . $match[2];
			}
		}

		if (empty($serverList))
		{
			trigger_error('Session: Memcached server pool is empty.', E_USER_ERROR);

			return FALSE;
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Read
	 *
	 * Reads session data and acquires a lock
	 *
	 * @param  string $session_id Session ID
	 * @return string Serialized session data
	 */
	public function read($session_id)
	{
		if (isset($this->memcached) && $this->getLock($session_id))
		{
			// Needed by write() to detect session_regenerate_id() calls
			$this->sessionId = $session_id;

			$data = (string) $this->memcached->get($this->keyPrefix . $session_id);
			$this->fingerprint = md5($data);

			return $data;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Write
	 *
	 * Writes (create / update) session data
	 *
	 * @param  string $sessionId Session ID
	 * @param  string $data      Serialized session data
	 * @return boolean
	 */
	public function write($sessionId, $data)
	{
		if ( ! isset($this->memcached))
		{
			return FALSE;
		}
		// Was the ID regenerated?
		elseif ($sessionId !== $this->sessionId)
		{
			if ( ! $this->releaseLock() OR ! $this->getLock($sessionId))
			{
				return FALSE;
			}

			$this->fingerprint = md5('');
			$this->sessionId = $sessionId;
		}

		if (isset($this->lockKey))
		{
			$this->memcached->replace($this->lockKey, time(), 300);
			if ($this->fingerprint !== ($fingerprint = md5($data)))
			{
				if ($this->memcached->set($this->keyPrefix . $sessionId, $data, $this->options['expiration']))
				{
					$this->fingerprint = $fingerprint;

					return TRUE;
				}

				return FALSE;
			}

			return $this->memcached->touch($this->keyPrefix . $sessionId, $this->options['expiration']);
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Close
	 *
	 * Releases locks and closes connection.
	 *
	 * @return boolean
	 */
	public function close()
	{
		if (isset($this->memcached))
		{
			isset($this->lockKey) && $this->memcached->delete($this->lockKey);
			if ( ! $this->memcached->quit())
			{
				return FALSE;
			}

			$this->memcached = NULL;

			return TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Destroy
	 *
	 * Destroys the current session.
	 *
	 * @param  string $sessionId Session ID
	 * @return boolean
	 */
	public function destroy($sessionId)
	{
		if (isset($this->memcached, $this->lockKey))
		{
			$this->memcached->delete($this->keyPrefix . $sessionId);

			return $this->cookieDestroy();
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Garbage Collector
	 *
	 * Deletes expired sessions
	 *
	 * @param  integer $maxLifetime Maximum lifetime of sessions
	 * @return boolean
	 */
	public function gc($maxLifetime)
	{
		// Not necessary, Memcached takes care of that.
		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get lock
	 *
	 * Acquires an (emulated) lock.
	 *
	 * @param  string $sessionId Session ID
	 * @return boolean
	 */
	protected function getLock($sessionId)
	{
		if (isset($this->lockKey))
		{
			return $this->memcached->replace($this->lockKey, time(), 300);
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lockKey = $this->keyPrefix . $sessionId . ':lock';
		$attempt = 0;
		do
		{
			if ($this->memcached->get($lockKey))
			{
				sleep(1);
				continue;
			}

			if ( ! $this->memcached->set($lockKey, time(), 300))
			{
				trigger_error('Session: Error while trying to obtain lock for ' . $this->keyPrefix . $sessionId, E_USER_ERROR);

				return FALSE;
			}

			$this->lockKey = $lockKey;
			break;
		} while (++$attempt < 30);

		if ($attempt === 30)
		{
			trigger_error('Session: Unable to obtain lock for ' . $this->keyPrefix . $sessionId . ' after 30 attempts, aborting.', E_USER_ERROR);

			return FALSE;
		}

		$this->lock = TRUE;

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Release lock
	 *
	 * Releases a previously acquired lock
	 *
	 * @return boolean
	 */
	protected function releaseLock()
	{
		if (isset($this->memcached, $this->lockKey) && $this->lock)
		{
			if ( ! $this->memcached->delete($this->lockKey) && $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND)
			{
				trigger_error('Session: Error while trying to free lock for ' . $this->lockKey, E_USER_ERROR);

				return FALSE;
			}

			$this->lockKey = NULL;
			$this->lock = FALSE;
		}

		return TRUE;
	}
}
