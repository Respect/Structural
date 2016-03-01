<?php

namespace Respect\Structural\tests\Driver\MongoDb;

use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use Respect\Data\Collections\Collection;
use Respect\Structural\Driver as BaseDriver;
use Respect\Structural\Driver\MongoDb\MongoDbDriver;

class MongoDbDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MongoDbDriver
     */
    private $driver;

    protected function setUp()
    {
        if (!extension_loaded('mongo') || !extension_loaded('mongodb')) {
            $this->markTestSkipped('missing mongo or mongodb extension');
        }

        if (extension_loaded('mongodb') && !class_exists('\MongoDB\Client')) {
            $this->markTestSkipped('missing mongodb library');
        }

        parent::setUp();
        $client = $this
            ->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->driver = new MongoDbDriver($client, 'database');
    }

    public function testDriverShouldAnInstanceOfDriverInterface()
    {
        $this->assertInstanceOf(BaseDriver::class, $this->driver);
    }

    public function testGenerateQueryShouldReturnSimpleFind()
    {
        $result = $this->driver->generateQuery(Collection::my_coll());
        $this->assertEquals([], $result);
    }

    public function testGenerateQueryShouldReturnSimpleFindById()
    {
        $result = $this->driver->generateQuery(Collection::my_coll('56cf5c943f90a847400041ac'));
        $this->assertEquals(['_id' => new ObjectID('56cf5c943f90a847400041ac')], $result);
    }

    public function testGenerateQueryShouldUsePartialResultSets()
    {
        $result = $this->driver->generateQuery(Collection::article()->author['56cf5c943f90a847400041ac']);
        $this->assertEquals(['author._id' => new ObjectID('56cf5c943f90a847400041ac')], $result);
    }
}
