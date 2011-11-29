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
    function test_generate_query_should_return_simple_find_by_id()
    {
        $result = $this->mapper->generateQuery(Collection::my_coll(42));
        $this->assertEquals(
            array(
                'my_coll' => array('_id' => 42)
            ),
            $result
        );
    }
    function test_generate_query_should_use_partial_result_sets()
    {
        $result = $this->mapper->generateQuery(Collection::article()->author[42]);
        $this->assertEquals(
            array(
                'article' => array('author._id' => 42)
            ),
            $result
        );
    }
}