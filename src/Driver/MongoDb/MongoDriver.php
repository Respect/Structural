<?php

namespace Respect\Structural\Driver\MongoDb;

use Respect\Data\Collections\Collection;

class MongoDriver extends AbstractDriver
{
    /**
     * @var \MongoClient
     */
    private $connection;

    /**
     * @var \MongoDB
     */
    private $database;

    /**
     * Driver constructor.
     *
     * @param \MongoClient $connection
     * @param string       $database
     */
    public function __construct(\MongoClient $connection, $database)
    {
        $this->connection = $connection;
        $this->database = $connection->{$database};
    }

    /**
     * @return \MongoDB
     */
    public function getDatabase()
    {
        return $this->database;
    }

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
        $data = $cursor->current();
        $cursor->next();

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
        $cursor = $this->getDatabase()->{$collection}->find($query);
        $cursor->rewind();

        return $cursor;
    }

    /**
     * @param int|string $id
     *
     * @return \MongoId|\MongoInt32
     */
    public function createObjectId($id)
    {
        if (is_int($id)) {
            return new \MongoInt32($id);
        }

        return new \MongoId($id);
    }

    /**
     * @param Collection $collection
     * @param $document
     *
     * @return void
     */
    public function insert($collection, $document)
    {
        $this->getDatabase()->{$collection}->insert($document);
    }

    public function update($collection, $criteria, $document)
    {
        $this->getDatabase()->{$collection}->update($criteria, $document);
    }

    public function remove($collection, $criteria)
    {
        $this->getDatabase()->{$collection}->remove($criteria);
    }
}
