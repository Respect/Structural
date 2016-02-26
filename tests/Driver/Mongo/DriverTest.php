<?php

namespace Respect\Structural\Tests\Driver\Mongo;

use Respect\Data\Collections\Collection;
use Respect\Structural\Driver\Mongo\Driver;
use Respect\Structural\Driver as BaseDriver;

class DriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Driver
     */
    private $driver;

    protected function setUp()
    {
        if (!class_exists('\MongoClient')) {
            $this->markTestSkipped('missing legacy mongo extension driver');
        }

        parent::setUp();
        $client = $this
            ->getMockBuilder(\MongoClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->driver = new Driver($client, 'collection');

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
        $result = $this->driver->generateQuery(Collection::my_coll(42));
        $this->assertEquals(['_id' => new \MongoInt32(42)], $result);
    }

    public function testGenerateQueryShouldUsePartialResultSets()
    {
        $result = $this->driver->generateQuery(Collection::article()->author["56cf5c943f90a847400041ac"]);
        $this->assertEquals(['author._id' => new \MongoId("56cf5c943f90a847400041ac")], $result);
    }
}
