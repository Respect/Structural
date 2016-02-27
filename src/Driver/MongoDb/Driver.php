<?php

namespace Respect\Structural\Driver\MongoDb;

use Respect\Data\Collections\Collection;
use Respect\Structural\Driver as BaseDriver;
use Respect\Structural\Driver\Exception as DriverException;

class Driver implements BaseDriver
{
    /**
     * @var BaseDriver
     */
    private $connection;

    protected function __construct(BaseDriver $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $database
     * @param string $server
     * @param array  $options
     * @param array  $driverOptions
     *
     * @return Driver
     *
     * @throws DriverException
     */
    public static function factoryLegacy(
        $database,
        $server = 'mongodb://localhost:27017',
        array $options = ['connect' => false],
        array $driverOptions = []
    ) {
        if (!extension_loaded('mongo')) {
            throw DriverException::extensionNotLoaded('mongo');
        }

        $client = new \MongoClient($server, $options, $driverOptions);
        $driver = new MongoDriver($client, $database);

        return new self($driver);
    }

    /**
     * @param string $database
     * @param string $uri
     * @param array  $uriOptions
     * @param array  $driverOptions
     *
     * @return Driver
     *
     * @throws DriverException
     */
    public static function factory(
        $database,
        $uri = 'mongodb://localhost:27017',
        array $uriOptions = [],
        array $driverOptions = []
    ) {
        if (!extension_loaded('mongodb')) {
            throw DriverException::extensionNotLoaded('mongodb');
        }
        $client = new \MongoDB\Client($uri, $uriOptions, $driverOptions);
        $driver = new MongoDbDriver($client, $database);

        return new self($driver);
    }

    /**
     * @return Driver
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(\Iterator $cursor)
    {
        return $this->getConnection()->fetch($cursor);
    }

    /**
     * {@inheritdoc}
     */
    public function find($collection, array $query = [])
    {
        return $this->getConnection()->find($collection, $query);
    }

    /**
     * {@inheritdoc}
     */
    public function generateQuery(Collection $collection)
    {
        return $this->getConnection()->generateQuery($collection);
    }

    /**
     * @param Collection $collection
     * @param $document
     *
     * @return void
     */
    public function insert($collection, $document)
    {
        $this->getConnection()->insert($collection, $document);
    }

    /**
     * {@inheritdoc}
     */
    public function update($collection, $criteria, $document)
    {
        $this->getConnection()->update($collection, $criteria, $document);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($collection, $criteria)
    {
        $this->getConnection()->remove($collection, $criteria);
    }
}
