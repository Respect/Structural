<?php

namespace Respect\Structural\Tests\Driver\Mongo;

use Respect\Data\Collections\Collection;
use Respect\Structural\Driver as BaseDriver;
use Respect\Structural\Driver\MongoDb\MongoDriver;
use Respect\Structural\Tests\Driver\TestCase;

class MongoDriverTest extends TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('missing mongo extension');
        }

        parent::setUp();
    }

    public function createDriver($connection = null)
    {
        if (is_null($connection)) {
            $connection = $this->createConnection();
        }
        return new MongoDriver($connection, 'database');
    }

    public function getConnectionInterface()
    {
        return \MongoClient::class;
    }

    public function getMockConnectionRetrieveEmptyResult()
    {
        $collection = $this->getMockBuilder(\MongoCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['find'])
            ->getMock();
        $collection->expects($this->once())->method('find')->willReturn(new \ArrayIterator());

        $database = $this->getMockBuilder(\MongoDB::class)
            ->disableOriginalConstructor()
            ->setMethods(['selectCollection'])
            ->getMock();
        $database->expects($this->once())->method('selectCollection')->willReturn($collection);

        return $this->createConnection('selectDB', $database);
    }

    public function getMockConnectionRetrieveFilledResult()
    {
        $result = new \ArrayIterator([
            [
                '_id' => 1,
                'name' => 'Test',
            ],
        ]);
        $collection = $this->getMockBuilder(\MongoCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['find'])
            ->getMock();
        $collection->expects($this->once())->method('find')->willReturn($result);

        $database = $this->getMockBuilder(\MongoDB::class)
            ->disableOriginalConstructor()
            ->setMethods(['selectCollection'])
            ->getMock();
        $database->expects($this->once())->method('selectCollection')->willReturn($collection);

        return $this->createConnection('selectDB', $database);
    }

    public function getMockConnectionInsertOne()
    {
        $collection = $this->getMockBuilder(\MongoCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['insert'])
            ->getMock();
        $collection->expects($this->once())->method('insert')->willReturnCallback(function($document){
            $document->_id = new \MongoId('56d6fb233f90a8231f0041a9');
        });

        $database = $this->getMockBuilder(\MongoDB::class)
            ->disableOriginalConstructor()
            ->setMethods(['selectCollection'])
            ->getMock();
        $database->expects($this->once())->method('selectCollection')->willReturn($collection);

        return $this->createConnection('selectDB', $database);
    }

    public function getMockConnectionUpdateOne()
    {
        $collection = $this->getMockBuilder(\MongoCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['update'])
            ->getMock();
        $collection->expects($this->once())->method('update')->willReturn(null);

        $database = $this->getMockBuilder(\MongoDB::class)
            ->disableOriginalConstructor()
            ->setMethods(['selectCollection'])
            ->getMock();
        $database->expects($this->once())->method('selectCollection')->willReturn($collection);

        return $this->createConnection('selectDB', $database);
    }

    public function getMockConnectionRemoveOne()
    {
        $collection = $this->getMockBuilder(\MongoCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['remove'])
            ->getMock();
        $collection->expects($this->once())->method('remove')->willReturn(null);

        $database = $this->getMockBuilder(\MongoDB::class)
            ->disableOriginalConstructor()
            ->setMethods(['selectCollection'])
            ->getMock();
        $database->expects($this->once())->method('selectCollection')->willReturn($collection);

        return $this->createConnection('selectDB', $database);
    }


    public function provideGenerateQueryShouldReturnSimpleFindById()
    {
        return [
            'simple return' => [
                Collection::my_coll('56d6fb233f90a8231f0041a9'),
                [
                    '_id' => '56d6fb233f90a8231f0041a9'
                ]
            ]
        ];
    }

    public function provideCollectionAndSearchShouldRetrieveEmptyResult()
    {
        return [
            ['collection', ['_id' => 1]]
        ];
    }

    public function provideGenerateQueryShouldUsePartialResultSets()
    {
        return [
            'simple' => [
                Collection::article()->author['56d6fb233f90a8231f0041a9'],
                [
                    'author._id' => new \MongoId('56d6fb233f90a8231f0041a9'),
                ]
            ]
        ];
    }


    public function provideCollectionAndSearchShouldRetrieveFilledResult()
    {
        return [
            'simple result' => [
                'authors',
                ['_id' => 1],
                new \ArrayIterator([
                    [
                        '_id' => 1,
                        'name' => 'Test'
                    ]
                ])
            ],
        ];
    }
}
