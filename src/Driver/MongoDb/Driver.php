<?php

namespace Respect\Structural\Driver\MongoDb;

use MongoDB\BSON\ObjectID;
use MongoDB\Client as MongoDBClient;
use MongoDB\Database;
use Respect\Data\CollectionIterator;
use Respect\Data\Collections\Collection;
use Respect\Structural\Driver as BaseDriver;

class Driver implements BaseDriver
{
    /**
     * @var MongoDBClient
     */
    private $connection;

    /**
     * @var Database
     */
    private $database;

    /**
     * Driver constructor.
     *
     * @param MongoDBClient $connection
     * @param string        $database
     */
    public function __construct(MongoDBClient $connection, $database)
    {
        $this->connection = $connection;
        $this->database = $connection->selectDatabase($database);
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return MongoDBClient
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param \Iterator $cursor
     *
     * @return array
     */
    public function fetch(\Iterator $cursor)
    {
        $data = null;
        if ($cursor->valid()) {
            $data = $cursor->current();
            $cursor->next();
        }

        return $data;
    }

    /**
     * @param array $collection
     * @param array $query
     *
     * @return \Iterator
     */
    public function find($collection, array $query = [])
    {
        $cursor = $this->getDatabase()->selectCollection($collection)->find($query);
        $iterator = new \IteratorIterator($cursor);
        $iterator->rewind();

        return $iterator;
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    public function generateQuery(Collection $collection)
    {
        return $this->parseConditions($collection);
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    protected function parseConditions(Collection $collection)
    {
        $allCollections = CollectionIterator::recursive($collection);
        $allCollections = iterator_to_array($allCollections);
        $allCollections = array_slice($allCollections, 1);

        $condition = $this->getConditionArray($collection);

        foreach ($allCollections as $coll) {
            $condition += $this->getConditionArray($coll, true);
        }

        return $condition;
    }

    /**
     * @param Collection $collection
     * @param bool|false $prefix
     *
     * @return array
     */
    protected function getConditionArray(Collection $collection, $prefix = false)
    {
        $condition = $collection->getCondition();

        if (!is_array($condition)) {
            $condition = ['_id' => new ObjectID($condition)];
        }

        if ($prefix) {
            $condition = static::prefixArrayKeys($condition, $collection->getName() . '.');
        }

        return $condition;
    }

    /**
     * @param array  $array
     * @param string $prefix
     *
     * @return array
     */
    protected static function prefixArrayKeys(array $array, $prefix)
    {
        $new = [];

        foreach ($array as $key => $value) {
            $new["{$prefix}{$key}"] = $value;
        }

        return $new;
    }

    /**
     * @param Collection $collection
     * @param $document
     *
     * @return void
     */
    public function insert($collection, $document)
    {
        $result = $this->getDatabase()->selectCollection($collection)->insertOne($document);
        $document->_id = $result->getInsertedId();
    }

    public function update($collection, $criteria, $document)
    {
        $this->getDatabase()->selectCollection($collection)->updateOne($criteria, ['$set' => $document]);
    }

    /**
     * @param string $collection
     * @param array  $criteria
     */
    public function remove($collection, $criteria)
    {
        $this->getDatabase()->selectCollection($collection)->deleteOne($criteria);
    }
}
