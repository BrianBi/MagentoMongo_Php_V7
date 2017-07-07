<?php

/**
 * @category  Yaoli
 * @package   Yaoli_MongoCore
 */

require_once Mage::getBaseDir() . '/lib/MongoLib/autoload.php';

class Yaoli_MongoCore_Model_Resource_Connection_Adapter
{

    /**
     * @var int
     */
    const MR_COMMAND_TIMEOUT = 1000000;

    /**
     * The connection to the MongoDB instance
     *
     * @var Mongo
     */
    protected $_connection = null;


    /**
     * from the app/etc/local.xml into the document_db section
     *
     * @var array Config loaded from app/etc/local.xml
     */
    protected $_config = null;


    /**
     * Constructor which only init configuration
     *
     * @return void
     */
    public function __construct()
    {
        $this->_config = $this->_getConfig();
    }


    /**
     * @param int $retry The number of time the connection can be retried
     *
     * @throws MongoConnectionException If connection fails
     * @throws Mage_Core_Exception      If mandatory param are not set up
     *
     * @return Mongo The MongoDB database connection
     *
     */
    protected function _getConnection($retry=5)
    {

        if (!isset($this->_config['connection_string'])) {
            Mage::throwException('MongoDB is not configured yet (connection_string missing)');
        }

        if (!isset($this->_config['dbname'])) {
            Mage::throwException('MongoDB is not configured yet (dbname missing)');
        }

        if ($this->_connection === null) {
            try {
                //$this->_connection = new MongoClient($this->_getConnectionString(), $this->_getConnectionOptions());
                $this->_connection = new MongoDB\Driver\Manager($this->_config['connection_string']);
            } catch (MongoConnectionException $e) {
                if ($retry > 0) {
                    $this->_connection = null;
                    return $this->_getConnection($retry - 1);
                } else {
                    throw $e;
                }
            }
        }

        return $this->_connection;
    }

    /**
     * mongodb://{$username}:{$password}@{$host}
     *
     * @return array The address of the MongoDB we want to connect on
     */
    protected function _getConnectionString()
    {
        return $this->_config['connection_string'] . $this->_config['dbname'];
    }


    /**
     * @return array An array that contains all options applied to the MongoDB connection
     */
    protected function _getConnectionOptions()
    {
        $options = array();

        if (isset($this->_config['connection_options']) && is_array($this->_config['connection_options'])) {
            $options = $this->_config['connection_options'];
        }

        return $options;
    }


    /**
     * @return array The read configuration
     */
    protected function _getConfig()
    {
        return Mage::getConfig()->getNode('global/document_db')->asArray();
    }


    /**
     * @param string $collectionName The name of the collection to be accessed
     *
     * @return MongoCollection
     *
     * @since 0.0.1
     */
    public function getCollection($collectionName)
    {
        //return $this->_getConnection()->selectCollection($this->_config['dbname'], $collectionName);
        return new MongoDB\Collection($this->_getConnection(), $this->_config['dbname'], $collectionName);
    }


    /**
     * @param string    $sourceCollection The name of the collection to be processed by the MR command.
     * @param string    $outputCollection The name of the collection where the output of the MR command will be put.
     * @param MongoCode $map              JS code of the map operation as a MongoCode object.
     * @param MongoCode $reduce           JS code of the reduce operation as a MongoCode object.
     *                                    If not specified use self::_getDefaultReducer() to retrieve identity reducer.
     * @param MongoCode $finalize         JS code of the finalize operation as a MongoCode object. The finalize function is optionnal.
     * @param array     $query            The query the source collection must be filtered with before mapping process.
     * @param string    $outputMode       The output mode of the operation (replace, merge or reduce). Default mode is replace.
     *                                    More information into MongoDB documentation.
     *
     * @return Yaoli_MongoCore_Model_Resource_Connection_Adapter Self reference
     *
     * @since 0.0.1
     */
    public function runMapReduce($sourceCollection, $outputCollection, $map, $reduce,
        $finalize = null, $query = null, $outputMode = 'replace'
    ) {
        $mrParams = array(
            'mapreduce' => $sourceCollection,
            'out'       => array($outputMode => $outputCollection),
            'map'       => $map,
            'reduce'    => $reduce
        );

        if (!is_null($finalize)) {
            $mrParams['finalize'] = $finalize;
        }

        if (!is_null($query)) {
            $mrParams['query'] = $query;
        }

        $db = $this->_getConnection()->selectDb($this->_config['dbname']);
        $lastCommandResult = $db->command($mrParams, array('timeout' => self::MR_COMMAND_TIMEOUT));

        if (!$lastCommandResult['ok']) {
            Mage::throwException(
                "Map reduce operation failed : {$lastCommandResult['assertion']} (code={$lastCommandResult['assertionCode']})"
            );
        }

        return $lastCommandResult;
    }

    /**
     * Util class to build MongoDB queries
     *
     * @return Yaoli_MongoCore_Model_Resource_Connection_Query_Builder
     */
    public function getQueryBuilder()
    {
        return Mage::getResourceSingleton('mongocore/connection_query_builder');
    }
}
