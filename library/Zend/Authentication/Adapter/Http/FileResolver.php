<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Authentication
 */

namespace Zend\Authentication\Adapter\Http;

/**
 * HTTP Authentication File Resolver
 *
 * @category   Zend
 * @package    Zend_Authentication
 * @subpackage Adapter_Http
 */
class FileResolver implements ResolverInterface
{
    /**
     * Path to credentials file
     *
     * @var string
     */
    protected $_file;

    /**
     * Constructor
     *
     * @param  string $path Complete filename where the credentials are stored
     */
    public function __construct($path = '')
    {
        if (!empty($path)) {
            $this->setFile($path);
        }
    }

    /**
     * Set the path to the credentials file
     *
     * @param  string $path
     * @return FileResolver Provides a fluent interface
     * @throws Exception\ExceptionInterface
     */
    public function setFile($path)
    {
        if (empty($path) || !is_readable($path)) {
            throw new Exception\InvalidArgumentException('Path not readable: ' . $path);
        }
        $this->_file = $path;

        return $this;
    }

    /**
     * Returns the path to the credentials file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * Resolve credentials
     *
     * Only the first matching username/realm combination in the file is
     * returned. If the file contains credentials for Digest authentication,
     * the returned string is the password hash, or h(a1) from RFC 2617. The
     * returned string is the plain-text password for Basic authentication.
     *
     * The expected format of the file is:
     *   username:realm:sharedSecret
     *
     * That is, each line consists of the user's username, the applicable
     * authentication realm, and the password or hash, each delimited by
     * colons.
     *
     * @param  string $username Username
     * @param  string $realm    Authentication Realm
     * @return string|false User's shared secret, if the user is found in the
     *         realm, false otherwise.
     * @throws Exception\ExceptionInterface
     */
    public function resolve($username, $realm, $password = null)
    {
        if (empty($username)) {
            throw new Exception\InvalidArgumentException('Username is required');
        } else if (!ctype_print($username) || strpos($username, ':') !== false) {
            throw new Exception\InvalidArgumentException('Username must consist only of printable characters, '
                                                              . 'excluding the colon');
        }
        if (empty($realm)) {
            throw new Exception\InvalidArgumentException('Realm is required');
        } else if (!ctype_print($realm) || strpos($realm, ':') !== false) {
            throw new Exception\InvalidArgumentException('Realm must consist only of printable characters, '
                                                              . 'excluding the colon.');
        }

        // Open file, read through looking for matching credentials
        $fp = @fopen($this->_file, 'r');
        if (!$fp) {
            throw new Exception\RuntimeException('Unable to open password file: ' . $this->_file);
        }

        // No real validation is done on the contents of the password file. The
        // assumption is that we trust the administrators to keep it secure.
        while (($line = fgetcsv($fp, 512, ':')) !== false) {
            if ($line[0] == $username && $line[1] == $realm) {
                $password = $line[2];
                fclose($fp);
                return $password;
            }
        }

        fclose($fp);
        return false;
    }
}
