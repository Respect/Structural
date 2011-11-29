<?php

namespace Respect\Structural;
use Respect\Data\Collection;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    protected $mapper;
    function setUp()
    {
        $this->mapper = new Mapper;
    }
    function test_generate_query_should_return_simple_find()
    {
        $result = $this->mapper->generateQuery(Collection::my_coll());
        $this->assertEquals(
            array(
                'my_coll' => array()
            ),
            $result
        );
    }
}