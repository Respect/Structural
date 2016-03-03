<?php

namespace Respect\Structural\Tests\Driver;

use Respect\Data\Collections\Collection;
use Respect\Structural\Driver;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Driver
     */
    private $driver;

    protected function setUp()
    {
        parent::setUp();

        $this->driver = $this->createDriver();
    }

    /**
     * @param object $connection
     *
     * @return Driver
     */
    abstract public function createDriver($connection = null);

    /**
     * @return string
     */
    abstract public function getConnectionInterface();

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    abstract public function getMockConnectionRetrieveEmptyResult();

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    abstract public function getMockConnectionRetrieveFilledResult();

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    abstract public function getMockConnectionInsertOne();

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    abstract public function getMockConnectionUpdateOne();

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    abstract public function getMockConnectionRemoveOne();

    /**
     * @return array
     */
    abstract public function provideGenerateQueryShouldReturnSimpleFindById();

    /**
     * @return array
     */
    abstract public function provideCollectionAndSearchShouldRetrieveEmptyResult();

    /**
     * @return array
     */
    abstract public function provideGenerateQueryShouldUsePartialResultSets();

    /**
     * @return array
     */
    abstract public function provideCollectionAndSearchShouldRetrieveFilledResult();

    /**
     * @param string $method
     * @param mixed  $result
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createConnection($method = null, $result = null)
    {
        $methods = [];

        if ($method) {
            $methods[] = $method;
        }

        $client = $this
            ->getMockBuilder($this->getConnectionInterface())
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        if ($method && $result) {
            $client
                ->expects($this->once())
                ->method($method)
                ->will($this->returnValue($result));
        }

        return $client;
    }

    public function testDriverShouldAnInstanceOfDriverInterface()
    {
        $this->assertInstanceOf(Driver::class, $this->driver);
    }

    public function testRetrieveConnection()
    {
        $this->assertInstanceOf($this->getConnectionInterface(), $this->driver->getConnection());
    }

    public function testShouldRetrieveCurrentCursorValueAndNext()
    {
        $iterator = new \ArrayIterator(['a', 'b']);

        $this->assertEquals('a', $this->driver->fetch($iterator));
        $this->assertEquals('b', $iterator->current());
    }

    /**
     * @dataProvider provideCollectionAndSearchShouldRetrieveEmptyResult
     *
     * @param string $collection
     * @param array  $search
     */
    public function testFindRetrieveEmptyResult($collection, $search)
    {
        $driver = $this->createDriver($this->getMockConnectionRetrieveEmptyResult());

        $this->assertEmpty($driver->find($collection, $search));
    }

    /**
     * @dataProvider provideCollectionAndSearchShouldRetrieveFilledResult
     *
     * @param string $collection
     * @param array  $search
     */
    public function testFindRetrieveFilledResult($collection, $search, $expected)
    {
        $driver = $this->createDriver($this->getMockConnectionRetrieveFilledResult());

        $result = $driver->find($collection, $search);

        $this->assertInstanceOf(\Iterator::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals($expected, $result);
    }

    public function testGenerateQueryShouldReturnSimpleFind()
    {
        $result = $this->driver->generateQuery(Collection::my_coll());
        $this->assertEquals([], $result);
    }

    /**
     * @dataProvider provideGenerateQueryShouldReturnSimpleFindById
     *
     * @param Collection $collection
     * @param array      $expectedResult
     */
    public function testGenerateQueryShouldReturnSimpleFindById(Collection $collection, array $expectedResult)
    {
        $result = $this->driver->generateQuery($collection);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider provideGenerateQueryShouldUsePartialResultSets
     *
     * @param Collection $mappedCollection
     * @param array      $expectedResult
     */
    public function testGenerateQueryShouldUsePartialResultSets(Collection $mappedCollection, array $expectedResult)
    {
        $result = $this->driver->generateQuery($mappedCollection);
        $this->assertEquals($expectedResult, $result);
    }

    public function testInsertDataShouldRetrieveId()
    {
        $data = new \stdClass();
        $data->name = 'Test';

        $this->createDriver($this->getMockConnectionInsertOne())->insert('author', $data);

        $this->assertObjectHasAttribute('_id', $data);
    }

    public function testUpdateDataShouldWithSuccess()
    {
        $data = new \stdClass();
        $data->name = 'Test';

        $this->createDriver($this->getMockConnectionUpdateOne())->update('author', ['name' => 'Test'], $data);
    }

    public function testRemoveDataShouldWithSuccess()
    {
        $this->createDriver($this->getMockConnectionRemoveOne())->remove('author', ['name' => 'Test']);
    }
}
