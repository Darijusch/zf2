<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Test
 */

namespace Zend\Test\PHPUnit\Db\DataSet;

/**
 * Aggregate several Zend_Db_Table instances into a dataset.
 *
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 */
class DbTableDataSet extends \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
    /**
     * @var array
     */
    protected $tables = array();

    /**
     * Add a Table dataset representation by specifiying an arbitrary select query.
     *
     * By default a select * will be done on the given tablename.
     *
     * @param \Zend\Db\Table\AbstractTable $table
     * @param string|\Zend\Db\Select $query
     * @param string $where
     * @param string $order
     * @param string $count
     * @param string $offset
     */
    public function addTable(\Zend\Db\Table\AbstractTable $table, $where = null, $order = null, $count = null, $offset = null)
    {
        $tableName = $table->info('name');
        $this->tables[$tableName] = new DbTable($table, $where, $order, $count, $offset);
    }

    /**
     * Creates an iterator over the tables in the data set. If $reverse is
     * true a reverse iterator will be returned.
     *
     * @param bool $reverse
     * @return PHPUnit_Extensions_Database_DB_TableIterator
     */
    protected function createIterator($reverse = \FALSE)
    {
        return new \PHPUnit\Extensions\Database\DataSet\DefaultTableIterator($this->tables, $reverse);
    }

    /**
     * Returns a table object for the given table.
     *
     * @param string $tableName
     * @return PHPUnit_Extensions_Database_DB_Table
     */
    public function getTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            throw new \Zend\Test\PHPUnit\Db\Exception\InvalidArgumentException(
            	"$tableName is not a table in the current database."
            );
        }

        return $this->tables[$tableName];
    }

    /**
     * Returns a list of table names for the database
     *
     * @return Array
     */
    public function getTableNames()
    {
        return array_keys($this->tables);
    }
}
