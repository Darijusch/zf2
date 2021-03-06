<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Feed
 */

namespace Zend\Feed\Writer;

use DateTime;
use Zend\Uri;

/**
* @category Zend
* @package Zend_Feed_Writer
*/
class Deleted
{

    /**
     * Internal array containing all data associated with this entry or item.
     *
     * @var array
     */
    protected $_data = array();
    
    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $_type = null;
    
    /**
     * Set the feed character encoding
     *
     * @return string|null
     * @throws Exception\InvalidArgumentException
     */
    public function setEncoding($encoding)
    {
        if (empty($encoding) || !is_string($encoding)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['encoding'] = $encoding;
    }

    /**
     * Get the feed character encoding
     *
     * @return string|null
     */
    public function getEncoding()
    {
        if (!array_key_exists('encoding', $this->_data)) {
            return 'UTF-8';
        }
        return $this->_data['encoding'];
    }
    
    /**
     * Unset a specific data point
     *
     * @param string $name
     */
    public function remove($name)
    {
        if (isset($this->_data[$name])) {
            unset($this->_data[$name]);
        }
    }
    
    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }
    
    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set reference
     *
     * @param $reference
     * @throws Exception\InvalidArgumentException
     */
    public function setReference($reference)
    {
        if (empty($reference) || !is_string($reference)) {
            throw new Exception\InvalidArgumentException('Invalid parameter: reference must be a non-empty string');
        }
        $this->_data['reference'] = $reference;
    }
    
    public function getReference()
    {
        if (!array_key_exists('reference', $this->_data)) {
            return null;
        }
        return $this->_data['reference'];
    }

    /**
     * Set when
     *
     * @param null|string|DateTime $date
     * @throws Exception\InvalidArgumentException
     */
    public function setWhen($date = null)
    {
        if ($date === null) {
            $date = new DateTime();
        } elseif (is_int($date)) {
            $date = new DateTime('@' . $date);
        } elseif (!$date instanceof DateTime) {
            throw new Exception\InvalidArgumentException('Invalid DateTime object or UNIX Timestamp'
            . ' passed as parameter');
        }
        $this->_data['when'] = $date;
    }

    /**
     * @return \DateTime
     */
    public function getWhen()
    {
        if (!array_key_exists('when', $this->_data)) {
            return null;
        }
        return $this->_data['when'];
    }

    /**
     * Set by
     *
     * @param array $by
     * @throws Exception\InvalidArgumentException
     */
    public function setBy(array $by)
    {
        $author = array();
        if (!array_key_exists('name', $by) 
            || empty($by['name']) 
            || !is_string($by['name'])
        ) {
            throw new Exception\InvalidArgumentException('Invalid parameter: author array must include a'
            . ' "name" key with a non-empty string value');
        }
        $author['name'] = $by['name'];
        if (isset($by['email'])) {
            if (empty($by['email']) || !is_string($by['email'])) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "email" array'
                . ' value must be a non-empty string');
            }
            $author['email'] = $by['email'];
        }
        if (isset($by['uri'])) {
            if (empty($by['uri']) 
                || !is_string($by['uri']) 
                || !Uri\UriFactory::factory($by['uri'])->isValid()
            ) {
                throw new Exception\InvalidArgumentException('Invalid parameter: "uri" array value must'
                 . ' be a non-empty string and valid URI/IRI');
            }
            $author['uri'] = $by['uri'];
        }
        $this->_data['by'] = $author;
    }
    
    public function getBy()
    {
        if (!array_key_exists('by', $this->_data)) {
            return null;
        }
        return $this->_data['by'];
    }
    
    public function setComment($comment)
    {
        $this->_data['comment'] = $comment;
    }
    
    public function getComment()
    {
        if (!array_key_exists('comment', $this->_data)) {
            return null;
        }
        return $this->_data['comment'];
    }

}
