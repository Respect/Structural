<?php

namespace Respect\Structural\Driver\Mongo;

use Respect\Data\CollectionIterator;
use Respect\Data\Collections\Collection;
use Respect\Structural\Driver as BaseDriver;

class Driver implements BaseDriver
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
     * @param \MongoClient $connection
     * @param string $database
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
     * @return array
     */
    public function fetch(\Iterator $cursor)
    {
        $cursor->next();
        return $cursor->current();
    }

    /**
     * @param array $collection
     * @param array $query
     * @return \Iterator
     */
    public function find($collection, array $query = array())
    {
        return $this->getDatabase()->{$collection}->find($query);
    }

    /**
     * @param Collection $collection
     * @return array
     */
    public function generateQuery(Collection $collection)
    {
        return $this->parseConditions($collection);
    }

    /**
     * @param Collection $collection
     * @return array
     */
    protected function parseConditions(Collection $collection)
    {
        $allCollections = CollectionIterator::recursive($collection);
        $allCollections = iterator_to_array($allCollections);
        $allCollections = array_slice($allCollections, 1);

        $condition = $this->getConditionArray($collection);

        foreach ($allCollections as $name => $coll)
            $condition += $this->getConditionArray($coll, true);

        return $condition;
    }

    /**
     * @param Collection $collection
     * @param bool|false $prefix
     * @return array
     */
    protected function getConditionArray(Collection $collection, $prefix = false)
    {
        $condition = $collection->getCondition();

        if (!is_array($condition)) {
            $condition = array('_id' => $this->createMongoId($condition));
        }

        if ($prefix)
            $condition = static::prefixArrayKeys($condition, $collection->getName() . ".");

        return $condition;
    }

    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    protected static function prefixArrayKeys(array $array, $prefix)
    {
        $new = array();

        foreach ($array as $key => $value)
            $new["{$prefix}{$key}"] = $value;

        return $new;
    }

    /**
     * @param int|string $id
     * @return \MongoId|\MongoInt32
     */
    protected function createMongoId($id)
    {
        if (is_int($id)) {
            return new \MongoInt32($id);
        }
        return new \MongoId($id);
    }

    /**
     * @param Collection $collection
     * @param $document
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
