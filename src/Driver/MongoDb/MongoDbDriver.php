<?php

namespace Respect\Structural\Driver\MongoDb;

use MongoDB\BSON\ObjectID;
use MongoDB\Client as MongoDBClient;
use MongoDB\Database;
use Respect\Data\Collections\Collection;

class MongoDbDriver extends AbstractDriver
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
     * @return \MongoDB\Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param int|string $id
     *
     * @return ObjectID
     */
    public function createObjectId($id)
    {
        return new ObjectID($id);
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
     * @param string $collection
     * @param array $query
     *
     * @return \Iterator
     */
    public function find($collection, array $query = [])
    {
        $cursor = $this->getDatabase()->selectCollection($collection)->find($query);
        $iterator = new \ArrayIterator($cursor);
        $iterator->rewind();

        return $iterator;
    }

    /**
     * @param string $collection
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
