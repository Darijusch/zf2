<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_OpenId
 */

namespace Zend\OpenId\Provider\Storage;

use Zend\OpenId;

/**
 * External storage implemmentation using serialized files
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Provider
 */
class File extends AbstractStorage
{

    /**
     * Directory name to store data files in
     *
     * @var string $_dir
     */
    private $_dir;

    /**
     * Constructs storage object and creates storage directory
     *
     * @param string $dir directory name to store data files in
     * @throws OpenId\Exception\RuntimeException
     */
    public function __construct($dir = null)
    {
        if ($dir === null) {
            $tmp = getenv('TMP');
            if (empty($tmp)) {
                $tmp = getenv('TEMP');
                if (empty($tmp)) {
                    $tmp = "/tmp";
                }
            }
            $user = get_current_user();
            if (is_string($user) && !empty($user)) {
                $tmp .= '/' . $user;
            }
            $dir = $tmp . '/openid/provider';
        }
        $this->_dir = $dir;
        if (!is_dir($this->_dir)) {
            if (!@mkdir($this->_dir, 0700, 1)) {
                throw new OpenId\Exception\RuntimeException(
                    "Cannot access storage directory $dir",
                    OpenId\Exception\RuntimeException::ERROR_STORAGE);
            }
        }
        if (($f = fopen($this->_dir.'/assoc.lock', 'w+')) === null) {
            throw new OpenId\Exception\RuntimeException(
                'Cannot create a lock file in the directory ' . $dir,
                OpenId\Exception\RuntimeException::ERROR_STORAGE);
        }
        fclose($f);
        if (($f = fopen($this->_dir.'/user.lock', 'w+')) === null) {
            throw new OpenId\Exception\RuntimeException(
                'Cannot create a lock file in the directory ' . $dir,
                OpenId\Exception\RuntimeException::ERROR_STORAGE);
        }
        fclose($f);
    }

    /**
     * Stores information about session identified by $handle
     *
     * @param string $handle assiciation handle
     * @param string $macFunc HMAC function (sha1 or sha256)
     * @param string $secret shared secret
     * @param string $expires expiration UNIX time
     * @return bool
     */
    public function addAssociation($handle, $macFunc, $secret, $expires)
    {
        $name = $this->_dir . '/assoc_' . md5($handle);
        $lock = @fopen($this->_dir . '/assoc.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'w+');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $data = serialize(array($handle, $macFunc, $secret, $expires));
            fwrite($f, $data);
            fclose($f);
            fclose($lock);
            return true;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Gets information about association identified by $handle
     * Returns true if given association found and not expired and false
     * otherwise
     *
     * @param string $handle assiciation handle
     * @param string &$macFunc HMAC function (sha1 or sha256)
     * @param string &$secret shared secret
     * @param string &$expires expiration UNIX time
     * @return bool
     */
    public function getAssociation($handle, &$macFunc, &$secret, &$expires)
    {
        $name = $this->_dir . '/assoc_' . md5($handle);
        $lock = @fopen($this->_dir . '/assoc.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedHandle, $macFunc, $secret, $expires) = unserialize($data);
                if ($handle === $storedHandle && $expires > time()) {
                    $ret = true;
                } else {
                    fclose($f);
                    @unlink($name);
                    fclose($lock);
                    return false;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Removes information about association identified by $handle
     *
     * @param string $handle assiciation handle
     * @return bool
     */
    public function delAssociation($handle)
    {
        $name = $this->_dir . '/assoc_' . md5($handle);
        $lock = @fopen($this->_dir . '/assoc.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            @unlink($name);
            fclose($lock);
            return true;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Register new user with given $id and $password
     * Returns true in case of success and false if user with given $id already
     * exists
     *
     * @param string $id user identity URL
     * @param string $password encoded user password
     * @return bool
     */
    public function addUser($id, $password)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'x');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $data = serialize(array($id, $password, array()));
            fwrite($f, $data);
            fclose($f);
            fclose($lock);
            return true;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Returns true if user with given $id exists and false otherwise
     *
     * @param string $id user identity URL
     * @return bool
     */
    public function hasUser($id)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_SH)) {
            fclose($lock);
            return false;
        }
        try { 
            $f = @fopen($name, 'r');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $trusted) = unserialize($data);
                if ($id === $storedId) {
                    $ret = true;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Verify if user with given $id exists and has specified $password
     *
     * @param string $id user identity URL
     * @param string $password user password
     * @return bool
     */
    public function checkUser($id, $password)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_SH)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $trusted) = unserialize($data);
                if ($id === $storedId && $password === $storedPassword) {
                    $ret = true;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Removes information abou specified user
     *
     * @param string $id user identity URL
     * @return bool
     */
    public function delUser($id)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            @unlink($name);
            fclose($lock);
            return true;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Returns array of all trusted/untrusted sites for given user identified
     * by $id
     *
     * @param string $id user identity URL
     * @return array
     */
    public function getTrustedSites($id)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_SH)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $trusted) = unserialize($data);
                if ($id === $storedId) {
                    $ret = $trusted;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Stores information about trusted/untrusted site for given user
     *
     * @param string $id user identity URL
     * @param string $site site URL
     * @param mixed $trusted trust data from extension or just a boolean value
     * @return bool
     */
    public function addSite($id, $site, $trusted)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r+');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $sites) = unserialize($data);
                if ($id === $storedId) {
                    if ($trusted === null) {
                        unset($sites[$site]);
                    } else {
                        $sites[$site] = $trusted;
                    }
                    rewind($f);
                    ftruncate($f, 0);
                    $data = serialize(array($id, $storedPassword, $sites));
                    fwrite($f, $data);
                    $ret = true;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (\Exception $e) {
            fclose($lock);
            throw $e;
        }
    }
}
