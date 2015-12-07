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
namespace Sapphire\Utilities;

/**
 * File
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class File
{
    /**
     * Read File
     *
     * Opens the file specified in the path and returns it as a string.
     * This function is DEPRECATED and should be removed in Use file_get_contents() instead.
     *
     * @access public
     * @param  string $file path to file
     * @return string
     */
    public static function read($file)
    {
        return @file_get_contents($file);
    }

    // ------------------------------------------------------------------------

    /**
     * Write File
     *
     * Writes data to the file specified in the path.
     * Creates a new file if non-existent.
     *
     * @access public
     * @param  string $path path to file
     * @param  string $data file data
     * @param  string $mode
     * @return bool
     */
    public static function write($path, $data, $mode = 'wb')
    {
        if ( ! $fp = @fopen($path, $mode))
        {
            return FALSE;
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Delete Files
     *
     * Deletes all files contained in the supplied directory path.
     * Files must be writable or owned by the system in order to be deleted.
     * If the second parameter is set to true, any directories contained
     * within the supplied base directory will be nuked as well.
     *
     * @access public
     * @param  string  $path path to file
     * @param  boolean $delDir whether to delete any directories found in the path
     * @param  integer $level
     * @param  boolean $htdocs whether to skip deleting .htaccess and index page files
     * @return boolean
     */
    public static function delete($path, $delDir = FALSE, $level = 0, $htdocs = FALSE)
    {
        // Trim the trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if ( ! $currentDir = @opendir($path))
        {
            return FALSE;
        }

        while (FALSE !== ($filename = @readdir($currentDir)))
        {
            if ($filename !== '.' && $filename !== '..')
            {
                if (is_dir($path . DIRECTORY_SEPARATOR . $filename) && $filename[0] !== '.')
                {
                    self::delete($path . DIRECTORY_SEPARATOR . $filename, $delDir, $level + 1, $htdocs);
                }
                elseif ($htdocs !== TRUE OR ! preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename))
                {
                    @unlink($path . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }

        @closedir($currentDir);

        if ($delDir === TRUE && $level > 0)
        {
            return @rmdir($path);
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get File names
     *
     * Reads the specified directory and builds an array containing the file names.
     * Any sub-folders contained within the specified path are read as well.
     *
     * @access public
     * @param  string  $sourceDir path to source
     * @param  boolean $includePath whether to include the path as part of the filename
     * @param  boolean $recursion internal variable to determine recursion status - do not use in calls
     * @return array
     */
    public static function getNames($sourceDir, $includePath = FALSE, $recursion = FALSE)
    {
        static $fileData = [];

        if ($fp = @opendir($sourceDir))
        {
            // reset the array and make sure $sourceDir has a trailing slash on the initial call
            if ($recursion === FALSE)
            {
                $fileData = [];
                $sourceDir = rtrim(realpath($sourceDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            while (FALSE !== ($file = readdir($fp)))
            {
                if (@is_dir($sourceDir . $file) && $file[0] !== '.')
                {
                    self::getNames($sourceDir . $file . DIRECTORY_SEPARATOR, $includePath, TRUE);
                }
                elseif ($file[0] !== '.')
                {
                    $fileData[] = ($includePath === TRUE) ? $sourceDir . $file : $file;
                }
            }

            closedir($fp);

            return $fileData;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Directory File Information
     *
     * Reads the specified directory and builds an array containing the file names,
     * file size, dates, and permissions
     * Any sub-folders contained within the specified path are read as well.
     *
     * @access public
     * @param  string  $sourceDir path to source
     * @param  boolean $topLevelOnly Look only at the top level directory specified?
     * @param  boolean $recursion internal variable to determine recursion status - do not use in calls
     * @return array
     */
    public static function getDirInfo($sourceDir, $topLevelOnly = TRUE, $recursion = FALSE)
    {
        static $fileData = [];
        $relativePath = $sourceDir;

        if ($fp = @opendir($sourceDir))
        {
            // reset the array and make sure $sourceDir has a trailing slash on the initial call
            if ($recursion === FALSE)
            {
                $fileData = [];
                $sourceDir = rtrim(realpath($sourceDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            // foreach (scandir($sourceDir, 1) as $file) // In addition to being PHP5+, scandir() is simply not as fast
            while (FALSE !== ($file = readdir($fp)))
            {
                if (@is_dir($sourceDir . $file) && $file[0] !== '.' && $topLevelOnly === FALSE)
                {
                    self::getDirInfo($sourceDir . $file . DIRECTORY_SEPARATOR, $topLevelOnly, TRUE);
                }
                elseif ($file[0] !== '.')
                {
                    $fileData[$file] = self::getInfo($sourceDir . $file);
                    $fileData[$file]['relative_path'] = $relativePath;
                }
            }

            closedir($fp);

            return $fileData;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get File Info
     *
     * Given a file and path, returns the name, path, size, date modified
     * Second parameter allows you to explicitly declare what information you want returned
     * Options are: name, server_path, size, date, readable, writable, executable, fileperms
     * Returns false if the file cannot be found.
     *
     * @access public
     * @param  string $file path to file
     * @param  mixed  $returnedValues array or comma separated string of information returned
     * @return array
     */
    public static function getInfo($file, $returnedValues = ['name', 'server_path', 'size', 'date'])
    {
        if ( ! file_exists($file))
        {
            return FALSE;
        }

        $fileInfo = [];

        if (is_string($returnedValues))
        {
            $returnedValues = explode(',', $returnedValues);
        }

        foreach ($returnedValues as $key)
        {
            switch ($key)
            {
                case 'name':
                    $fileInfo['name'] = substr(strrchr($file, DIRECTORY_SEPARATOR), 1);
                    break;
                case 'server_path':
                    $fileInfo['server_path'] = $file;
                    break;
                case 'size':
                    $fileInfo['size'] = filesize($file);
                    break;
                case 'date':
                    $fileInfo['date'] = filemtime($file);
                    break;
                case 'readable':
                    $fileInfo['readable'] = is_readable($file);
                    break;
                case 'writable':
                    // There are known problems using is_writable on IIS.  It may not be reliable - consider fileperms()
                    $fileInfo['writable'] = is_writable($file);
                    break;
                case 'executable':
                    $fileInfo['executable'] = is_executable($file);
                    break;
                case 'fileperms':
                    $fileInfo['fileperms'] = fileperms($file);
                    break;
            }
        }

        return $fileInfo;
    }

    // ------------------------------------------------------------------------

    /**
     * Tests for file writability
     *
     * is_writable() returns TRUE on Windows servers when you really can't write to
     * the file, based on the read-only attribute. is_writable() is also unreliable
     * on Unix servers if safe_mode is on.
     *
     * @link   https://bugs.php.net/bug.php?id=54709
     * @access public
     * @param  string $file
     * @return boolean
     */
    public static function isReallyWritable($file)
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR === '/' && (PHP_VERSION_ID > 50400 OR ! ini_get('safe_mode')))
        {
            return is_writable($file);
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it. Bah...
         */
        if (is_dir($file))
        {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE)
            {
                return FALSE;
            }

            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);

            return TRUE;
        }
        elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE)
        {
            return FALSE;
        }

        fclose($fp);

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Symbolic Permissions
     *
     * Takes a numeric value representing a file's permissions and returns
     * standard symbolic notation representing that value
     *
     * @access public
     * @param  integer $perms
     * @return string
     */
    public static function symbolicPermissions($perms)
    {
        if (($perms & 0xC000) === 0xC000)
        {
            $symbolic = 's'; // Socket
        }
        elseif (($perms & 0xA000) === 0xA000)
        {
            $symbolic = 'l'; // Symbolic Link
        }
        elseif (($perms & 0x8000) === 0x8000)
        {
            $symbolic = '-'; // Regular
        }
        elseif (($perms & 0x6000) === 0x6000)
        {
            $symbolic = 'b'; // Block special
        }
        elseif (($perms & 0x4000) === 0x4000)
        {
            $symbolic = 'd'; // Directory
        }
        elseif (($perms & 0x2000) === 0x2000)
        {
            $symbolic = 'c'; // Character special
        }
        elseif (($perms & 0x1000) === 0x1000)
        {
            $symbolic = 'p'; // FIFO pipe
        }
        else
        {
            $symbolic = 'u'; // Unknown
        }

        // Owner
        $symbolic .= (($perms & 0x0100) ? 'r' : '-') . (($perms & 0x0080) ? 'w' : '-') . (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $symbolic .= (($perms & 0x0020) ? 'r' : '-') . (($perms & 0x0010) ? 'w' : '-') . (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

        // World
        $symbolic .= (($perms & 0x0004) ? 'r' : '-') . (($perms & 0x0002) ? 'w' : '-') . (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

        return $symbolic;
    }

    // ------------------------------------------------------------------------

    /**
     * Octal Permissions
     *
     * Takes a numeric value representing a file's permissions and returns
     * a three character string representing the file's octal permissions
     *
     * @access public
     * @param  integer
     * @return string
     */
    public static function octalPermissions($perms)
    {
        return substr(sprintf('%o', $perms), -3);
    }
}
