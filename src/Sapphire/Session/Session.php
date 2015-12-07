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
 * Session Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Session
{
	protected $handler = 'file';
	protected $options;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  array $options Configuration parameters
	 */
	public function __construct($options = [])
	{
		// No sessions under CLI
		if (PHP_SAPI == 'cli')
		{
			return;
		}
		elseif ((bool) ini_get('session.auto_start'))
		{
			return;
		}
		elseif ( ! empty($options['handler']))
		{
			$this->handler = $options['handler'];
			unset($options['handler']);
		}

		$class = __NAMESPACE__ . '\Handler\\' . ucfirst($this->handler);

		// Configuration ...
		$this->configure($options);

		if (class_exists($class))
		{
			$class = new $class($options);
		}
		else
		{
			trigger_error("Session: Handler '" . $this->handler . "' Not Found. Aborting.", E_USER_ERROR);
		}

		if ($class instanceof SessionHandlerInterface)
		{
			if (version_compare(PHP_VERSION, '5.4', '>='))
			{
				session_set_save_handler($class, TRUE);
			}
			else
			{
				session_set_save_handler(
					[$class, 'open'],
					[$class, 'close'],
					[$class, 'read'],
					[$class, 'write'],
					[$class, 'destroy'],
					[$class, 'gc']
				);

				register_shutdown_function('session_write_close');
			}
		}
		else
		{
			trigger_error("Session: Handler '" . $this->handler . "' doesn't implement SessionHandlerInterface. Aborting.", E_USER_ERROR);
		}

		// Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
		if (isset($_COOKIE[$this->options['cookie_name']])
			&& (
				! is_string($_COOKIE[$this->options['cookie_name']])
				OR ! preg_match('/^[0-9a-f]{40}$/', $_COOKIE[$this->options['cookie_name']])
			)
		)
		{
			unset($_COOKIE[$this->options['cookie_name']]);
		}

		session_start();

		// Is session ID auto-regeneration configured? (ignoring ajax requests)
		if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
			&& ($regenerate_time = $this->options['time_to_update']) > 0
		)
		{
			if ( ! isset($_SESSION['__Sapphire_last_regenerate']))
			{
				$_SESSION['__Sapphire_last_regenerate'] = time();
			}
			elseif ($_SESSION['__Sapphire_last_regenerate'] < (time() - $regenerate_time))
			{
				$this->regenerate((boolean) $this->options['regenerate_destroy']);
			}
		}
		// Another work-around ... PHP doesn't seem to send the session cookie
		// unless it is being currently created or regenerated
		elseif (isset($_COOKIE[$this->options['cookie_name']]) && $_COOKIE[$this->options['cookie_name']] === session_id())
		{
			setcookie(
				$this->options['cookie_name'],
				session_id(),
				(empty($this->options['cookie_lifetime']) ? 0 : time() + $this->options['cookie_lifetime']),
				$this->options['cookie_path'],
				$this->options['cookie_domain'],
				$this->options['cookie_secure'],
				TRUE
			);
		}

		$this->initVars();
	}

	// ------------------------------------------------------------------------

	/**
	 * Configuration
	 *
	 * Handle input parameters and configuration defaults
	 *
	 * @param  array &$options Input parameters
	 * @return void
	 */
	protected function configure(&$options)
	{
		$expiration = isset($options['expiration']) ? $options['expiration'] : 0;
		isset($options['save_path']) OR $options['save_path'] = NULL;
		isset($options['match_ip']) OR $options['match_ip'] = FALSE;
		isset($options['time_to_update']) OR $options['time_to_update'] = 300;
		isset($options['regenerate_destroy']) OR $options['regenerate_destroy'] = FALSE;

		if (isset($options['cookie_lifetime']))
		{
			$options['cookie_lifetime'] = (int) $options['cookie_lifetime'];
		}
		else
		{
			$options['cookie_lifetime'] = ( ! isset($expiration) && $options['sess_expire_on_close'])
				? 0 : (int) $expiration;
		}

		if (empty($options['cookie_name']))
		{
			$options['cookie_name'] = ini_get('session.name');
		}
		else
		{
			ini_set('session.name', $options['cookie_name']);
		}

		isset($options['cookie_path']) OR $options['cookie_path'] = '/';
		isset($options['cookie_domain']) OR $options['cookie_domain'] = '';
		isset($options['cookie_secure']) OR $options['cookie_secure'] = FALSE;

		session_set_cookie_params(
			$options['cookie_lifetime'],
			$options['cookie_path'],
			$options['cookie_domain'],
			$options['cookie_secure'],
			TRUE // HttpOnly; Yes, this is intentional and not configurable for security reasons
		);

		if (empty($expiration))
		{
			$options['expiration'] = (int) ini_get('session.gc_maxlifetime');
		}
		else
		{
			$options['expiration'] = (int) $expiration;
			ini_set('session.gc_maxlifetime', $expiration);
		}

		$this->options = $options;

		// Security is king
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_strict_mode', 1);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.hash_function', 1);
		ini_set('session.hash_bits_per_character', 4);
	}

	// ------------------------------------------------------------------------

	/**
	 * Handle temporary variables
	 *
	 * Clears old "flash" data, marks the new one for deletion and handles
	 * "temp" data deletion.
	 *
	 * @access protected
	 * @return void
	 */
	protected function initVars()
	{
		if ( ! empty($_SESSION['__Sapphire_vars']))
		{
			$current_time = time();

			foreach ($_SESSION['__Sapphire_vars'] as $key => &$value)
			{
				if ($value === 'new')
				{
					$_SESSION['__Sapphire_vars'][$key] = 'old';
				}
				elseif ($value < $current_time)
				{
					unset($_SESSION[$key], $_SESSION['__Sapphire_vars'][$key]);
				}
			}

			if (empty($_SESSION['__Sapphire_vars']))
			{
				unset($_SESSION['__Sapphire_vars']);
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Mark as flash
	 *
	 * @access public
	 * @param  mixed $key Session data key(s)
	 * @return boolean
	 */
	public function markAsFlash($key)
	{
		if (is_array($key))
		{
			for ($i = 0, $c = count($key); $i < $c; $i++)
			{
				if ( ! isset($_SESSION[$key[$i]]))
				{
					return FALSE;
				}
			}

			$new = array_fill_keys($key, 'new');

			$_SESSION['__Sapphire_vars'] = isset($_SESSION['__Sapphire_vars'])
				? array_merge($_SESSION['__Sapphire_vars'], $new)
				: $new;

			return TRUE;
		}

		if ( ! isset($_SESSION[$key]))
		{
			return FALSE;
		}

		$_SESSION['__Sapphire_vars'][$key] = 'new';

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get flash keys
	 *
	 * @access public
	 * @return array
	 */
	public function getFlashKeys()
	{
		if ( ! isset($_SESSION['__Sapphire_vars']))
		{
			return [];
		}

		$keys = [];
		foreach (array_keys($_SESSION['__Sapphire_vars']) as $key)
		{
			is_int($_SESSION['__Sapphire_vars'][$key]) OR $keys[] = $key;
		}

		return $keys;
	}

	// ------------------------------------------------------------------------

	/**
	 * Un mark flash
	 *
	 * @access public
	 * @param  mixed $key Session data key(s)
	 * @return void
	 */
	public function unMarkFlash($key)
	{
		if (empty($_SESSION['__Sapphire_vars']))
		{
			return;
		}

		is_array($key) OR $key = [$key];

		foreach ($key as $k)
		{
			if (isset($_SESSION['__Sapphire_vars'][$k]) && ! is_int($_SESSION['__Sapphire_vars'][$k]))
			{
				unset($_SESSION['__Sapphire_vars'][$k]);
			}
		}

		if (empty($_SESSION['__Sapphire_vars']))
		{
			unset($_SESSION['__Sapphire_vars']);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Mark as temp
	 *
	 * @access public
	 * @param  mixed   $key Session data key(s)
	 * @param  integer $ttl Time-to-live in seconds
	 * @return boolean
	 */
	public function markAsTemp($key, $ttl = 300)
	{
		$ttl += time();

		if (is_array($key))
		{
			$temp = [];

			foreach ($key as $k => $v)
			{
				// Do we have a key => ttl pair, or just a key?
				if (is_int($k))
				{
					$k = $v;
					$v = $ttl;
				}
				else
				{
					$v += time();
				}

				if ( ! isset($_SESSION[$k]))
				{
					return FALSE;
				}

				$temp[$k] = $v;
			}

			$_SESSION['__Sapphire_vars'] = isset($_SESSION['__Sapphire_vars'])
				? array_merge($_SESSION['__Sapphire_vars'], $temp)
				: $temp;

			return TRUE;
		}

		if ( ! isset($_SESSION[$key]))
		{
			return FALSE;
		}

		$_SESSION['__Sapphire_vars'][$key] = $ttl;

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Get temp keys
	 *
	 * @access public
	 * @return array
	 */
	public function getTempKeys()
	{
		if ( ! isset($_SESSION['__Sapphire_vars']))
		{
			return [];
		}

		$keys = [];
		foreach (array_keys($_SESSION['__Sapphire_vars']) as $key)
		{
			is_int($_SESSION['__Sapphire_vars'][$key]) && $keys[] = $key;
		}

		return $keys;
	}

	// ------------------------------------------------------------------------

	/**
	 * Un mark flash
	 *
	 * @access public
	 * @param  mixed $key Session data key(s)
	 * @return void
	 */
	public function unMarkTemp($key)
	{
		if (empty($_SESSION['__Sapphire_vars']))
		{
			return;
		}

		is_array($key) OR $key = [$key];

		foreach ($key as $k)
		{
			if (isset($_SESSION['__Sapphire_vars'][$k]) && is_int($_SESSION['__Sapphire_vars'][$k]))
			{
				unset($_SESSION['__Sapphire_vars'][$k]);
			}
		}

		if (empty($_SESSION['__Sapphire_vars']))
		{
			unset($_SESSION['__Sapphire_vars']);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Session destroy
	 *
	 * @access public
	 * @return void
	 */
	public function destroy()
	{
		session_destroy();
	}

	// ------------------------------------------------------------------------

	/**
	 * Session regenerate
	 *
	 * @access public
	 * @param  boolean $destroy Destroy old session data flag
	 * @return void
	 */
	public function regenerate($destroy = FALSE)
	{
		$_SESSION['__Sapphire_last_regenerate'] = time();
		session_regenerate_id($destroy);
	}

	// ------------------------------------------------------------------------

	/**
	 * Get user data
	 *
	 * @access public
	 * @param  string $key Session data key
	 * @return mixed  Session data value or NULL if not found
	 */
	public function get($key = NULL)
	{
		if (isset($key))
		{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
		}
		elseif (empty($_SESSION))
		{
			return [];
		}

		$data = [];
		$exclude = array_merge(
			['__Sapphire_vars'],
			$this->getFlashKeys(),
			$this->getTempKeys()
		);

		foreach (array_keys($_SESSION) as $key)
		{
			if ( ! in_array($key, $exclude, TRUE))
			{
				$data[$key] = $_SESSION[$key];
			}
		}

		return $data;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set user data
	 *
	 * @access public
	 * @param  mixed $data  Session data key or an associative array
	 * @param  mixed $value Value to store
	 * @return void
	 */
	public function set($data, $value = NULL)
	{
		if (is_array($data))
		{
			foreach ($data as $key => &$value)
			{
				$_SESSION[$key] = $value;
			}

			return;
		}

		$_SESSION[$data] = $value;
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete user data
	 *
	 * @access public
	 * @param  mixed $key Session data key(s)
	 * @return void
	 */
	public function delete($key)
	{
		if (is_array($key))
		{
			foreach ($key as $k)
			{
				unset($_SESSION[$k]);
			}

			return;
		}

		unset($_SESSION[$key]);
	}

	// ------------------------------------------------------------------------

	/**
	 * Has user data
	 *
	 * @access public
	 * @param  string $key Session data key
	 * @return boolean
	 */
	public function exist($key)
	{
		return isset($_SESSION[$key]);
	}

	// ------------------------------------------------------------------------

	/**
	 * Flash data (fetch)
	 *
	 * @access public
	 * @param  string $key Session data key
	 * @return mixed  Session data value or NULL if not found
	 */
	public function getFlash($key = NULL)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__Sapphire_vars'], $_SESSION['__Sapphire_vars'][$key], $_SESSION[$key]) && ! is_int($_SESSION['__Sapphire_vars'][$key]))
				? $_SESSION[$key]
				: NULL;
		}

		$data = [];

		if ( ! empty($_SESSION['__Sapphire_vars']))
		{
			foreach ($_SESSION['__Sapphire_vars'] as $key => &$value)
			{
				is_int($value) OR $data[$key] = $_SESSION[$key];
			}
		}

		return $data;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set flash data
	 *
	 * @access public
	 * @param  mixed $data  Session data key or an associative array
	 * @param  mixed $value Value to store
	 * @return void
	 */
	public function setFlash($data, $value = NULL)
	{
		$this->set($data, $value);
		$this->markAsFlash(is_array($data) ? array_keys($data) : $data);
	}

	// ------------------------------------------------------------------------

	/**
	 * Keep flash data
	 *
	 * @access public
	 * @param  mixed $key Session data key(s)
	 * @return void
	 */
	public function keepFlash($key)
	{
		$this->markAsFlash($key);
	}

	// ------------------------------------------------------------------------

	/**
	 * Get Temp data
	 *
	 * @access public
	 * @param  string $key Session data key
	 * @return mixed Session data value or NULL if not found
	 */
	public function getTemp($key = NULL)
	{
		if (isset($key))
		{
			return (isset($_SESSION['__Sapphire_vars'], $_SESSION['__Sapphire_vars'][$key], $_SESSION[$key]) && is_int($_SESSION['__Sapphire_vars'][$key]))
				? $_SESSION[$key]
				: NULL;
		}

		$data = [];

		if ( ! empty($_SESSION['__Sapphire_vars']))
		{
			foreach ($_SESSION['__Sapphire_vars'] as $key => &$value)
			{
				is_int($value) && $data[$key] = $_SESSION[$key];
			}
		}

		return $data;
	}

	// ------------------------------------------------------------------------

	/**
	 * Set temp data
	 *
	 * @access public
	 * @param  mixed   $data  Session data key or an associative array of items
	 * @param  mixed   $value Value to store
	 * @param  integer $ttl   Time-to-live in seconds
	 * @return void
	 */
	public function setTemp($data, $value = NULL, $ttl = 300)
	{
		$this->set($data, $value);
		$this->markAsTemp(is_array($data) ? array_keys($data) : $data, $ttl);
	}

	// ------------------------------------------------------------------------

	/**
	 * Unset temp data
	 *
	 * @access public
	 * @param  mixed $key Session data key(s)
	 * @return void
	 */
	public function unsetTempData($key)
	{
		$this->unMarkTemp($key);
	}
}
