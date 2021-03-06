<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Queue
 */

namespace Zend\Queue;

/**
 * Class for managing queue messages
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Message
 */
class Message
{
    /**
     * The data for the queue message
     *
     * @var array
     */
    protected $_data = array();

     /**
     * Connected is true if we have a reference to a live
     * \Zend\Queue\Adapter object.
     * This is false after the Message has been deserialized.
     *
     * @var boolean
     */
    protected $_connected = true;

    /**
     * \Zend\Queue\Queue parent class or instance
     *
     * @var \Zend\Queue\Queue
     */
    protected $_queue = null;

    /**
     * Name of the class of the \Zend\Queue\Queue
     *
     * @var string
     */
    protected $_queueClass = null;

    /**
     * Constructor
     *
     * @param  array $options
     * @throws \Zend\Queue\Exception
     */
    public function __construct(array $options = array())
    {
        if (isset($options['queue'])) {
            if ($options['queue'] instanceof Queue) {
                $this->_queue      = $options['queue'];
                $this->_queueClass = get_class($this->_queue);
            } else {
                $result = gettype($options['queue']);
                if ($result === 'object') {
                    $result = get_class($options['queue']);
                }

                throw new Exception\InvalidArgumentException(
                    '$options[\'queue\'] = ' . $result . ': must be instanceof \Zend\Queue\Queue'
                );
            }
        }
        if (isset($options['data'])) {
            if (!is_array($options['data'])) {
                throw new Exception\InvalidArgumentException('Data must be an array');
            }
            $this->_data = $options['data'];
        }
    }

    /**
     * Retrieve message field value
     *
     * @param  string $key The user-specified key name.
     * @return string      The corresponding key value.
     * @throws \Zend\Queue\Exception if the $key is not a column in the message.
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_data)) {
            throw new Exception\InvalidArgumentException("Specified field \"$key\" is not in the message");
        }
        return $this->_data[$key];
    }

    /**
     * Set message field value
     *
     * @param  string $key   The message key.
     * @param  mixed  $value The value for the property.
     * @return void
     * @throws \Zend\Queue\Exception
     */
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->_data)) {
            throw new Exception\InvalidArgumentException("Specified field \"$key\" is not in the message");
        }
        $this->_data[$key] = $value;
    }

    /**
     * Test existence of message field
     *
     * @param  string  $key The column key.
     * @return boolean
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->_data);
    }

    /*
     * Serialize
     */

    /**
     * Store queue and data in serialized object
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_queueClass', '_data');
    }

    /**
     * Setup to do on wakeup.
     * A de-serialized Message should not be assumed to have access to a live
     * queue connection, so set _connected = false.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_connected = false;
    }

     /**
     * Returns the queue object, or null if this is disconnected message
     *
     * @return \Zend\Queue\Queue|null
     */
    public function getQueue()
    {
        return $this->_queue;
    }

    /**
     * Set the queue object, to re-establish a live connection
     * to the queue for a Message that has been de-serialized.
     *
     * @param  \Zend\Queue\Queue $queue
     * @return boolean
     */
    public function setQueue(Queue $queue)
    {
        $queueClass        = get_class($queue);
        $this->_queue      = $queue;
        $this->_queueClass = $queueClass;
        $this->_connected  = true;
        return true;
    }

    /**
     * Query the class name of the Queue object for which this
     * Message was created.
     *
     * @return string
     */
    public function getQueueClass()
    {
        return $this->_queueClass;
    }

    /**
     * Returns the column/value data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Sets all data in the row from an array.
     *
     * @param  array $data
     * @return \Zend\Queue\Message\Message Provides a fluent interface
     */
    public function setFromArray(array $data)
    {
        foreach ($data as $columnName => $value) {
            $this->$columnName = $value;
        }

        return $this;
    }
}
