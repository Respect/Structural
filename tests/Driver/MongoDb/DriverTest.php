<?php

namespace Respect\Structural\tests\Driver\MongoDb;

use Respect\Data\Collections\Collection;
use Respect\Structural\Driver\MongoDb\Driver;
use Respect\Structural\Driver\MongoDb\MongoDbDriver;
use Respect\Structural\Driver\MongoDb\MongoDriver;

class DriverTest extends \PHPUnit_Framework_TestCase
{

    public function testDriverShouldAnInstanceOfMongoLegacyDriver()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('missing mongo extension');
        }

        $driverWrapper = Driver::factoryLegacy('database');
        $this->assertInstanceOf(MongoDriver::class, $driverWrapper->getConnection());
    }

    public function testDriverShouldAnInstanceOfMongoDbDriver()
    {
        if (!extension_loaded('mongodb')) {
            $this->markTestSkipped('missing mongo extension');
        }

        $driverWrapper = Driver::factory('database');
        $this->assertInstanceOf(MongoDbDriver::class, $driverWrapper->getConnection());
    }

    /**
     * @param string $method
     * @param array  $arguments
     * @dataProvider provideActionMethodsAndRespectiveArguments
     */
    public function testDriverShouldCallMethodFromConnection($method, $arguments)
    {
        $mockDriver = $this->getMockForAbstractClass(\Respect\Structural\Driver::class);
        $mockDriver->expects($this->once())->method($method);

        $driver = $this
            ->getMockBuilder(Driver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();
        $driver->expects($this->once())->method('getConnection')->willReturn($mockDriver);
        call_user_func_array([$driver, $method], $arguments);
    }

    public function provideActionMethodsAndRespectiveArguments()
    {
        return [
            'insert' => ['insert', ['collection', []]],
            'update' => ['update', ['collection', [], []]],
            'remove' => ['remove', ['collection', []]],
            'fetch' => ['fetch', [new \IteratorIterator(new \ArrayObject())]],
            'find' => ['find', ['collection', []]],
            'generateQuery' => ['generateQuery', [Collection::coll()]],
        ];
    }
}
