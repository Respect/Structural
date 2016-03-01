<?php

namespace Respect\Structural\tests\Driver\DynamoDb;

use Aws\DynamoDb\DynamoDbClient;
use Respect\Data\Collections\Collection;
use Respect\Structural\Driver as BaseDriver;
use Respect\Structural\Driver\DynamoDb\Driver;

class DriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var \Aws\DynamoDb\DynamoDbClient
     */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = $this
            ->getMockBuilder(DynamoDbClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->driver = new Driver($this->client);
    }

    public function testDriverShouldAnInstanceOfDriverInterface()
    {
        $this->assertInstanceOf(BaseDriver::class, $this->driver);
    }

    public function testRetrieveConnection()
    {
        $this->assertSame($this->client, $this->driver->getConnection());
    }

    public function testShouldRetrieveCurrentCursorValueAndNext()
    {
        $iterator = new \ArrayIterator(['a', 'b']);

        $this->assertEquals('a', $this->driver->fetch($iterator));
        $this->assertEquals('b', $iterator->current());
    }

    public function testFindRetrieveEmptyIterator()
    {
        $result = new \Aws\Result([
            'Count' => 0
        ]);

        $client = $this
            ->getMockBuilder(DynamoDbClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['scan'])
            ->getMock();

        $client->expects($this->once())
            ->method('scan')
            ->will($this->returnValue($result));

        $driver = new Driver($client);

        $response = $driver->find('authors', ['_id' => 1]);

        $this->assertEmpty($response);
    }

    public function testFindRetrieveFormatedData()
    {
        $result = new \Aws\Result([
            'Items' => [
                [
                    '_id'   => ['N' => '1'],
                    'name'  => ['S' => 'Test']
                ]
            ],
            'Count' => 1
        ]);

        $client = $this
            ->getMockBuilder(DynamoDbClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['scan'])
            ->getMock();

        $client->expects($this->once())
            ->method('scan')
            ->will($this->returnValue($result));

        $driver = new Driver($client);

        $response = $driver->find('authors', ['_id' => 1]);

        $expected = new \ArrayIterator([
            [
                '_id'  => 1,
                'name' => 'Test'
            ]
        ]);

        $this->assertEquals($expected, $response);
    }

    public function testGenerateQueryShouldReturnSimpleFind()
    {
        $result = $this->driver->generateQuery(Collection::my_coll());
        $this->assertEquals([], $result);
    }

    public function testGenerateQueryShouldReturnSimpleFindById()
    {
        $collection = Collection::my_coll(42);

        $result = $this->driver->generateQuery($collection);

        $expected = [
            '_id' => [
                'AttributeValueList'    => [
                    ['N' => 42]
                ],
                'ComparisonOperator'    => 'EQ'
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGenerateQueryShouldUsePartialResultSets()
    {
        $result = $this->driver->generateQuery(Collection::article()->author[42]);
        $expected = [
            '_id' => [
                'AttributeValueList'    => [
                    ['N' => 42]
                ],
                'ComparisonOperator'    => 'EQ'
            ]
        ];
        $this->assertEquals($expected, $result);
    }
}
