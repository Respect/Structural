<?php

namespace Respect\Structural\Driver\DynamoDb;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;
use Respect\Data\CollectionIterator;
use Respect\Data\Collections\Collection;
use Respect\Structural\Driver as BaseDriver;

class Driver implements BaseDriver
{
    /**
     * @var \Aws\DynamoDb\DynamoDbClient
     */
    private $connection;

    /**
     * @var \Aws\DynamoDb\Marshaler
     */
    private $marshaler;

    /**
     * @var UuidFactoryInterface
     */
    private $uuid;

    /**
     * Driver constructor.
     *
     * @param \Aws\DynamoDb\DynamoDbClient $connection
     */
    public function __construct(DynamoDbClient $connection, UuidFactoryInterface $uuid)
    {
        $this->connection = $connection;
        $this->marshaler = new Marshaler();
        $this->uuid = $uuid;
    }

    /**
     * @return \Aws\DynamoDb\DynamoDbClient
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
        $data = [];

        if ($cursor->valid()) {
            $data = $cursor->current();
            $cursor->next();
        }

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
        $expression = [
            'TableName' => $collection,
        ];

        if (!empty($query)) {
            $expression['ScanFilter'] = $query;
        }

        $result = $this->getConnection()->scan($expression);

        return $this->formatResults($result);
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    public function generateQuery(Collection $collection)
    {
        return $this->parseConditions($collection);
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    protected function parseConditions(Collection $collection)
    {
        $collections = iterator_to_array(
            CollectionIterator::recursive($collection)
        );

        $collections = array_slice($collections, 1);
        $condition = $this->getConditionArray($collection);

        foreach ($collections as $name => $coll) {
            $condition += $this->getConditionArray($coll);
        }

        return $condition;
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    protected function getConditionArray(Collection $collection)
    {
        $condition = $collection->getCondition();

        if (!is_array($condition)) {
            $condition = ['_id' => $condition];
        }

        $conditions = [];

        foreach ($condition as $key => $value) {
            $conditions = [
                $key => [
                    'AttributeValueList' => [
                        $this->marshaler->marshalValue($value),
                    ],
                    'ComparisonOperator' => 'EQ',
                ],
            ];
        }

        return $conditions;
    }

    /**
     * @param Collection $collection
     * @param $document
     */
    public function insert($collection, $document)
    {
        $document->_id = $this->uuid->uuid4()->toString();

        $args = [
            'TableName' => $collection,
            'Item' => $this->marshaler->marshalItem($document),
        ];

        $this->getConnection()->putItem($args);
    }

    /**
     * @param Collection $collection
     * @param $criteria
     * @param $document
     */
    public function update($collection, $criteria, $document)
    {
        $args = [
            'TableName' => $collection,
            'Key' => $this->marshaler->marshalItem($criteria),
            'AttributeUpdates' => $this->formatAttributes($document),
        ];

        $this->getConnection()->updateItem($args);
    }

    /**
     * @param Collection $collection
     * @param $criteria
     */
    public function remove($collection, $criteria)
    {
        $args = [
            'TableName' => $collection,
            'Key' => $this->marshaler->marshalItem($criteria),
        ];

        $this->getConnection()->deleteItem($args);
    }

    /**
     * @param $values
     *
     * @return array
     */
    protected function formatAttributes($values)
    {
        $attributes = [];

        foreach ($values as $key => $value) {
            $attributes[$key] = [
                'Value' => $this->marshaler->marshalValue($value),
            ];
        }

        return $attributes;
    }

    protected function formatResults(\Aws\Result $result)
    {
        $items = new \ArrayIterator();

        if ($result['Count'] === 0) {
            return $items;
        }

        foreach ($result['Items'] as $item) {
            $items[] = $this->marshaler->unmarshalItem($item);
        }

        return $items;
    }
}
