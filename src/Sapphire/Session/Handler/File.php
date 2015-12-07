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
 * Session Handler for File
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class File extends SessionHandler
{
    /**
     * Save path
     *
     * @var string
     */
    protected $savePath;

    /**
     * File handle
     *
     * @var resource
     */
    protected $fileHandle;

    /**
     * File name
     *
     * @var resource
     */
    protected $filePath;

    /**
     * File new flag
     *
     * @var boolean
     */
    protected $fileNew;

    /**
     * Constructor
     *
     * @access public
     * @param  array $options Configuration parameters
     */
    public function __construct(&$options)
    {
        parent::__construct($options);

        if (isset($this->options['save_path']))
        {
            $this->options['save_path'] = rtrim($this->options['save_path'], '/\\');
            ini_set('session.save_path', $this->options['save_path']);
        }
        else
        {
            $this->options['save_path'] = rtrim(ini_get('session.save_path'), '/\\');
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Open
     *
     * Sanitizes the savePath directory.
     *
     * @access public
     * @param  string $savePath    Path to session files' directory
     * @param  string $sessionName Session cookie name
     * @return boolean
     */
    public function open($savePath, $sessionName)
    {
        if ( ! is_dir($savePath))
        {
            if ( ! mkdir($savePath, 0700, TRUE))
            {
                trigger_error("Session: Configured save path '" . $this->options['save_path'] . "' is not a directory, doesn't exist or cannot be created.", E_USER_ERROR);
            }
        }
        elseif ( ! is_writable($savePath))
        {
            trigger_error("Session: Configured save path '" . $this->options['save_path'] . "' is not writable by the PHP process.", E_USER_ERROR);
        }

        $this->options['save_path'] = $savePath;
        $this->filePath = $this->options['save_path'] . DIRECTORY_SEPARATOR
            . $sessionName // we'll use the session cookie name as a prefix to avoid collisions
            . ($this->options['match_ip'] ? md5($_SERVER['REMOTE_ADDR']) : '');

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Read
     *
     * Reads session data and acquires a lock
     *
     * @access public
     * @param  string $sessionId Session ID
     * @return string Serialized session data
     */
    public function read($sessionId)
    {
        // This might seem weird, but PHP 5.6 introduces session_reset(),
        // which re-reads session data
        if ($this->fileHandle === NULL)
        {
            // Just using fopen() with 'c+b' mode would be perfect, but it is only
            // available since PHP 5.2.6 and we have to set permissions for new files,
            // so we'd have to hack around this ...
            if (($this->fileNew = ! file_exists($this->filePath . $sessionId)) === TRUE)
            {
                if (($this->fileHandle = fopen($this->filePath . $sessionId, 'w+b')) === FALSE)
                {
                    echo("Session: File '" . $this->filePath . $sessionId . "' doesn't exist and cannot be created.");

                    return FALSE;
                }
            }
            elseif (($this->fileHandle = fopen($this->filePath . $sessionId, 'r+b')) === FALSE)
            {
                echo("Session: Unable to open file '" . $this->filePath . $sessionId . "'.");

                return FALSE;
            }

            if (flock($this->fileHandle, LOCK_EX) === FALSE)
            {
                echo("Session: Unable to obtain lock for file '" . $this->filePath . $sessionId . "'.");
                fclose($this->fileHandle);
                $this->fileHandle = NULL;

                return FALSE;
            }

            // Needed by write() to detect session_regenerate_id() calls
            $this->sessionId = $sessionId;

            if ($this->fileNew)
            {
                chmod($this->filePath . $sessionId, 0600);
                $this->fingerprint = md5('');

                return '';
            }
        }
        else
        {
            rewind($this->fileHandle);
        }

        $sessionData = '';
        for ($read = 0, $length = filesize($this->filePath . $sessionId); $read < $length; $read += strlen($buffer))
        {
            if (($buffer = fread($this->fileHandle, $length - $read)) === FALSE)
            {
                break;
            }

            $sessionData .= $buffer;
        }

        $this->fingerprint = md5($sessionData);

        return $sessionData;
    }

    // ------------------------------------------------------------------------

    /**
     * Write
     *
     * Writes (create / update) session data
     *
     * @access public
     * @param  string $sessionId Session ID
     * @param  string $data      Serialized session data
     * @return boolean
     */
    public function write($sessionId, $data)
    {
        // If the two IDs don't match, we have a session_regenerate_id() call
        // and we need to close the old handle and open a new one
        if ($sessionId !== $this->sessionId && ( ! $this->close() OR $this->read($sessionId) === FALSE))
        {
            return FALSE;
        }

        if ( ! is_resource($this->fileHandle))
        {
            return FALSE;
        }
        elseif ($this->fingerprint === md5($data))
        {
            return ($this->fileNew)
                ? TRUE
                : touch($this->filePath . $sessionId);
        }

        if ( ! $this->fileNew)
        {
            ftruncate($this->fileHandle, 0);
            rewind($this->fileHandle);
        }

        if (($length = strlen($data)) > 0)
        {
            for ($written = 0; $written < $length; $written += $result)
            {
                if (($result = fwrite($this->fileHandle, substr($data, $written))) === FALSE)
                {
                    break;
                }
            }

            if (isset($result) && ! is_int($result))
            {
                $this->fingerprint = md5(substr($data, 0, $written));
                echo('Session: Unable to write data.');

                return FALSE;
            }
        }

        $this->fingerprint = md5($data);

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Close
     *
     * Releases locks and closes file descriptor.
     *
     * @access public
     * @return boolean
     */
    public function close()
    {
        if (is_resource($this->fileHandle))
        {
            flock($this->fileHandle, LOCK_UN);
            fclose($this->fileHandle);

            $this->fileHandle = $this->fileNew = $this->sessionId = NULL;

            return TRUE;
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Destroy
     *
     * Destroys the current session.
     *
     * @access public
     * @param  string $sessionId Session ID
     * @return boolean
     */
    public function destroy($sessionId)
    {
        if ($this->close())
        {
            return file_exists($this->filePath . $sessionId)
                ? (unlink($this->filePath . $sessionId) && $this->cookieDestroy())
                : TRUE;
        }
        elseif ($this->filePath !== NULL)
        {
            clearstatcache();

            return file_exists($this->filePath . $sessionId)
                ? (unlink($this->filePath . $sessionId) && $this->cookieDestroy())
                : TRUE;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Garbage Collector
     *
     * Deletes expired sessions
     *
     * @access public
     * @param  integer $maxLifetime Maximum lifetime of sessions
     * @return boolean
     */
    public function gc($maxLifetime)
    {
        if ( ! is_dir($this->options['save_path']) OR ($directory = opendir($this->options['save_path'])) === FALSE)
        {
            echo("Session: Garbage collector couldn't list files under directory '" . $this->options['save_path'] . "'.");

            return FALSE;
        }

        $ts = time() - $maxLifetime;

        $pattern = sprintf(
            '/^%s[0-9a-f]{%d}$/',
            preg_quote($this->options['cookie_name'], '/'),
            ($this->options['match_ip'] === TRUE ? 72 : 40)
        );

        while (($file = readdir($directory)) !== FALSE)
        {
            // If the filename doesn't match this pattern, it's either not a session file or is not ours
            if ( ! preg_match($pattern, $file)
                OR ! is_file($this->options['save_path'] . DIRECTORY_SEPARATOR . $file)
                OR ($mtime = filemtime($this->options['save_path'] . DIRECTORY_SEPARATOR . $file)) === FALSE
                OR $mtime > $ts
            )
            {
                continue;
            }

            unlink($this->options['save_path'] . DIRECTORY_SEPARATOR . $file);
        }

        closedir($directory);

        return TRUE;
    }
}
